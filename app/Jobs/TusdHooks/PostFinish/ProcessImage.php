<?php

namespace App\Jobs\TusdHooks\PostFinish;

use App\Enums\MediaFolderTypes;
use App\Models\Image;
use App\Models\MediaFile;
use App\Services\File\FileService;
use App\Services\Image\ImageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcessImage implements ShouldQueue
{
    use Queueable;

    protected ImageService $imageService;
    protected FileService $fileService;
    protected $uploadData;
    public function __construct(array $uploadData, )
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
    }

    private function handleBody($fileName, $mimeType, $fileSize, $filePath, $infoPath): void
    {
        $uniqueBaseName = pathinfo($filePath, PATHINFO_BASENAME);
        try {
            DB::transaction(function () use ($fileName, $mimeType, $fileSize, $filePath, $infoPath, $uniqueBaseName) {
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                // imageからprev画像を生成
                $imagePrevPath = $this->imageService->generateImagePrev(MediaFolderTypes::IMAGES, $uniqueBaseName, $filePath);

                // 寸法を取得
                [$width, $height] = $this->imageService->getDimensions($filePath);

                // imagesへ保存
                $image = new Image();
                $image->extension = $extension;
                $image->width = $width;
                $image->height = $height;
                $image->save();

                // media_filesへの保存
                $mediaFile = new MediaFile();
                $mediaFile->title = pathinfo($fileName, PATHINFO_FILENAME);
                $mediaFile->base_name = $uniqueBaseName;
                $mediaFile->path = $filePath;
                $mediaFile->data_size = $fileSize;
                $mediaFile->mediable_type = Image::class;
                $mediaFile->mediable_id = $image->id;
                $mediaFile->preview_image_path = $imagePrevPath;
                $mediaFile->save();
            });
        } catch (\Throwable $e) {
            // 保存済みのファイルがある場合は削除
            if ($filePath && Storage::disk('private')->exists($filePath)) {
                logger()->error("エラー発生により保存したファイルを削除： {$filePath}");
                Storage::disk('private')->delete($filePath);
            }

            // 保存済みのExtrasフォルダがあれば削除
            if ($uniqueBaseName && Storage::disk('private')->directoryExists("extras/images/{$uniqueBaseName}")) {
                logger()->error("エラー発生により保存したディレクトリを削除： extras/images/{$uniqueBaseName}");
                Storage::disk('private')->deleteDirectory("extras/images/{$uniqueBaseName}");
            }

            throw $e;
        } finally {
            $this->fileService->deleteFile($infoPath);
        }
    }
}
