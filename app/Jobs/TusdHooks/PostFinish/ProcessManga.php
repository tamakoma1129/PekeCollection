<?php

namespace App\Jobs\TusdHooks\PostFinish;

use App\Enums\MediaFolderTypes;
use App\Events\MediaProcessedEvent;
use App\Jobs\ProcessMangaUpload;
use App\Models\Manga;
use App\Models\MangaPage;
use App\Models\MediaFile;
use App\Services\File\FileService;
use App\Services\Image\ImageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ProcessManga implements ShouldQueue
{
    use Queueable;

    protected ImageService $imageService;
    protected FileService $fileService;
    protected $uploadData;

    public function __construct(array $uploadData)
    {
        $this->uploadData = $uploadData;
    }

    public function handle(ImageService $imageService, FileService $fileService): void
    {
        $this->imageService = $imageService;
        $this->fileService = $fileService;

        $this->handleBody(
            $this->uploadData["fileName"],
            $this->uploadData["mimeType"],
            $this->uploadData["fileSize"],
            // path系はtusdコンテナからの絶対パスなので、privateからのパスに修正
            preg_replace("#^/private/#", "", $this->uploadData["path"]),
            preg_replace("#^/private/#", "", $this->uploadData["infoPath"])
        );

        event(new MediaProcessedEvent());
    }

    private function handleBody($fileName, $mimeType, $fileSize, $zipRelPath, $infoPath): void
    {
        $uniqueFolderName = pathinfo($zipRelPath, PATHINFO_FILENAME);
        $dirUploadRelPath = "uploads/mangas/{$uniqueFolderName}/";
        $dirExtraRelPath = "extras/mangas/{$uniqueFolderName}/";
        $zipFullPath = Storage::disk("private")->path($zipRelPath);
        $unzipFullPath = Storage::disk("private")->path(pathinfo($zipRelPath, PATHINFO_DIRNAME) . "/" . pathinfo($zipRelPath, PATHINFO_FILENAME));

        try {
            $this->unzipManga($zipFullPath, $unzipFullPath);
            $title = $fileName;

            $manga = DB::transaction(function () use (
                $title,
                $uniqueFolderName,
                $fileSize,
                $dirUploadRelPath,
                $dirExtraRelPath
            ) {
                // 解凍先にある名前一覧を昇順で取得(連番・存在・画像であることのバリデーション済み)
                $fileNames = $this->getUnzippedFiles(Storage::disk("private")->path($dirUploadRelPath));

                $pageAspectSizes = [];
                $mangaPages = [];

                foreach ($fileNames as $index => $fileName) {
                    $pageNumber = $index + 1;
                    $filePath = $dirUploadRelPath . $fileName;

                    // 最初のページをプレビューにする
                    if ($index === 0) {
                        $this->imageService->generateImagePrev(MediaFolderTypes::MANGAS, $uniqueFolderName, $filePath);
                    }

                    // 画像で何の比率が一番多いかを記録
                    [$width, $height] = $this->imageService->getDimensions($filePath);
                    $pageAspectSizes["{$width}x{$height}"] = ($pageAspectSizes["{$width}x{$height}"] ?? 0) + 1;

                    // MangaPage 作成
                    $mangaPage = new MangaPage();
                    $mangaPage->page_number    = $pageNumber;
                    $mangaPage->file_name      = $fileName;
                    $mangaPage->path           = $filePath;
                    $mangaPage->lite_path      = $dirExtraRelPath.pathinfo($fileName, PATHINFO_FILENAME).".webp";
                    $mangaPage->file_extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $mangaPage->file_size      = filesize(Storage::disk("private")->path($filePath));
                    $mangaPage->width          = $width;
                    $mangaPage->height         = $height;

                    $mangaPages[] = $mangaPage;
                }

                // 最も出現回数の多いディメンションで保存
                $mostCommonSize = array_keys($pageAspectSizes, max($pageAspectSizes))[0];
                [$mangaWidth, $mangaHeight] = explode('x', $mostCommonSize);

                // mangaを保存
                $manga = new Manga();
                $manga->title = $title;
                $manga->width = $mangaWidth;
                $manga->height = $mangaHeight;
                $manga->save();

                // MediaFile 作成
                $mediaFile = new MediaFile();
                $mediaFile->title               = $title;
                $mediaFile->base_name           = $uniqueFolderName;
                $mediaFile->path                = $dirUploadRelPath;
                $mediaFile->data_size           = $fileSize;
                $mediaFile->mediable_type       = Manga::class;
                $mediaFile->mediable_id         = $manga->id;
                $mediaFile->preview_image_path  = $dirExtraRelPath."prev.webp";
                $mediaFile->save();

                // MangaPageを一括DB登録
                $manga->pages()->saveMany($mangaPages);

                return $manga;
            });
        } catch (\Throwable $e) {
            // エラー時、オリジナルとExtrasフォルダを削除
            if ($dirUploadRelPath && Storage::disk('private')->directoryExists($dirUploadRelPath)) {
                logger()->error("エラー発生により保存したディレクトリを削除： {$dirUploadRelPath}");
                Storage::disk('private')->deleteDirectory($dirUploadRelPath);
            }
            if ($dirExtraRelPath && Storage::disk('private')->directoryExists($dirExtraRelPath)) {
                logger()->error("エラー発生により保存したディレクトリを削除： {$dirExtraRelPath}");
                Storage::disk('private')->deleteDirectory($dirExtraRelPath);
            }
            throw $e;
        } finally {
            $this->fileService->deleteFile($infoPath);
            $this->fileService->deleteFile($zipRelPath);
        }

        // 軽量ファイルの作成処理(非同期)
        ProcessMangaUpload::dispatch($manga->id);
    }

    private function unzipManga(string $zipFullPath, string $unzipFullPath)
    {
        $zip = new ZipArchive;
        if ($zip->open($zipFullPath) !== true) {
            throw new \Exception("ZIPファイルを開けません: {$zipFullPath}");
        }
        $zip->extractTo($unzipFullPath);
        $zip->close();
    }

    private function getUnzippedFiles(string $dirFullPath): array
    {
        $dirFullPath = rtrim($dirFullPath, '/');

        // scandirはデフォでアルファベット昇順ソートするので、natsortで自然ソートに
        $all = scandir($dirFullPath);
        natsort($all);
        $fileNames = [];
        // $allには「.」「..」も含まれるので、indexカウントは自分でやる
        $index = 0;
        foreach ($all as $fileName) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }

            $this->validation($dirFullPath . '/' . $fileName, $index);

            $fileNames[] = $fileName;
            $index++;
        }

        if (count($fileNames) === 0) {
            throw new \Exception("解凍先に画像が1枚もありません: {$dirFullPath}");
        }

        return $fileNames;
    }

    private function validation($dirFullPath, $index)
    {
        if (!is_file($dirFullPath)) {
            throw new \Exception("ファイルが存在しませんでした: {$dirFullPath}");
        }
        if (pathinfo($dirFullPath, PATHINFO_FILENAME) != $index + 1) {
            throw new \Exception("ファイル名が連番ではありませんでした: {$dirFullPath} != {$index}");
        }
        $mimeType = mime_content_type($dirFullPath);
        if (explode('/', $mimeType)[0] !== 'image') {
            throw new \Exception("漫画ファイルが画像ではありませんでした: {$dirFullPath}");
        }
    }
}
