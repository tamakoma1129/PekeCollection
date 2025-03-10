<?php

namespace App\Jobs\TusdHooks\PostFinish;

use App\Enums\MediaFolderTypes;
use App\Models\MediaFile;
use App\Models\Video;
use App\Services\File\FileService;
use App\Services\Image\ImageService;
use App\Services\Video\VideoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcessVideo implements ShouldQueue
{
    use Queueable;

    protected VideoService $videoService;
    protected ImageService $imageService;
    protected FileService $fileService;
    protected $uploadData;
    public function __construct(array $uploadData, )
    {
        $this->uploadData = $uploadData;
    }

    public function handle(VideoService $videoService, ImageService $imageService, FileService $fileService): void
    {
        $this->videoService = $videoService;
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
                // 長さを取得
                $videoDuration = $this->videoService->getDuration($filePath);
                // 解像度を取得
                [$videoWidth, $videoHeight] = $this->videoService->getResolution($filePath);

                // raw & prevイメージを取得し保存。
                $rawImagePath = $this->videoService->generateRawImage($filePath);
                $prevImagePath = $this->imageService->generateImagePrev(MediaFolderTypes::VIDEOS, $uniqueBaseName, $rawImagePath);
                $prevVideoPath = $this->videoService->generatePrevVideo($filePath, $videoDuration, $videoWidth, $videoHeight);

                // videosへ保存
                $video = new Video();
                $video->extension = $extension;
                $video->duration = $videoDuration;
                $video->width = $videoWidth;
                $video->height = $videoHeight;
                $video->raw_image_path = $rawImagePath;
                $video->preview_video_path = $prevVideoPath;
                $video->save();

                // media_filesへの保存
                $mediaFile = new MediaFile();
                $mediaFile->title = pathinfo($fileName, PATHINFO_FILENAME);
                $mediaFile->base_name = $uniqueBaseName;
                $mediaFile->path = $filePath;
                $mediaFile->data_size = $fileSize;
                $mediaFile->preview_image_path = $prevImagePath;
                $mediaFile->mediable_type = Video::class;
                $mediaFile->mediable_id = $video->id;
                $mediaFile->save();
            });
        } catch (\Throwable $e) {
            // 保存済みのファイルがある場合は削除
            if ($filePath && Storage::disk('private')->exists($filePath)) {
                logger()->error("エラー発生により保存したファイルを削除： {$filePath}");
                Storage::disk('private')->delete($filePath);
            }

            // 保存済みのExtrasフォルダがあれば削除
            if ($uniqueBaseName && Storage::disk('private')->directoryExists("extras/audios/{$uniqueBaseName}")) {
                logger()->error("エラー発生により保存したディレクトリを削除： extras/audios/{$uniqueBaseName}");
                Storage::disk('private')->deleteDirectory("extras/audios/{$uniqueBaseName}");
            }

            throw $e;
        } finally {
            $this->fileService->deleteFile($infoPath);
        }
    }
}
