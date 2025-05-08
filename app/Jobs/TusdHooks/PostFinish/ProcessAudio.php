<?php

namespace App\Jobs\TusdHooks\PostFinish;

use App\Enums\MediaFolderTypes;
use App\Events\MediaProcessedEvent;
use App\Jobs\GenerateWaveform;
use App\Models\Audio;
use App\Models\MediaFile;
use App\Services\Audio\AudioService;
use App\Services\File\FileService;
use App\Services\Image\ImageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcessAudio implements ShouldQueue
{
    use Queueable;

    protected AudioService $audioService;
    protected ImageService $imageService;
    protected FileService $fileService;
    protected $uploadData;
    public function __construct(array $uploadData)
    {
        $this->uploadData = $uploadData;
    }

    public function handle(AudioService $audioService, ImageService $imageService, FileService $fileService): void
    {
        $this->audioService = $audioService;
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

        event(new MediaProcessedEvent($this->uploadData["queueId"]));
    }

    private function handleBody($fileName, $mimeType, $fileSize, $filePath, $infoPath): void
    {
        $uniqueBaseName = pathinfo($filePath, PATHINFO_BASENAME);
        try {
            DB::transaction(function () use ($fileName, $mimeType, $fileSize, $filePath, $infoPath, $uniqueBaseName) {
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                // 長さを取得
                $audioDuration = $this->audioService->getDuration($filePath);

                // raw & prevイメージを取得し保存。
                $rawImagePath = $this->audioService->generateRawImage($filePath);
                if ($rawImagePath) {
                    $prevImagePath = $this->imageService->generateImagePrev(MediaFolderTypes::AUDIOS, $uniqueBaseName, $rawImagePath);
                } else {
                    $prevImagePath = null;
                }

                $prevAudioPath = $this->audioService->generatePrevAudio($filePath, $audioDuration);

                // audiosへ保存
                $audio = new Audio();
                $audio->extension = $extension;
                $audio->duration = $audioDuration;
                $audio->raw_image_path = $rawImagePath;
                $audio->preview_audio_path = $prevAudioPath;
                $audio->save();

                // media_filesへの保存
                $mediaFile = new MediaFile();
                $mediaFile->title = pathinfo($fileName, PATHINFO_FILENAME);
                $mediaFile->base_name = $uniqueBaseName;
                $mediaFile->path = $filePath;
                $mediaFile->data_size = $fileSize;
                $mediaFile->mediable_type = Audio::class;
                $mediaFile->mediable_id = $audio->id;
                $mediaFile->preview_image_path = $prevImagePath;
                $mediaFile->save();

                GenerateWaveform::dispatch($audio->id);
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
