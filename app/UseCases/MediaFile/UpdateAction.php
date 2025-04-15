<?php

namespace App\UseCases\MediaFile;

use App\Enums\MediaFolderTypes;
use App\Models\MediaFile;
use App\Services\File\FileService;
use App\Services\Image\ImageService;
use App\Services\Video\VideoService;
use Illuminate\Support\Facades\DB;

class UpdateAction
{
    protected FileService $fileService;
    protected ImageService $imageService;
    protected VideoService $videoService;
    public function __construct(FileService $fileService, ImageService $imageService, VideoService $videoService){
        $this->fileService = $fileService;
        $this->imageService = $imageService;
        $this->videoService = $videoService;
    }

    public function __invoke(MediaFile $mediaFile, array $validated)
    {
        if (array_key_exists('title', $validated)) {
            $mediaFile->title = $validated['title'];
        }
        if (array_key_exists('prev_time', $validated) && $mediaFile->mediable_type === "App\Models\Video") {
            $mediaFile->mediable->raw_image_path = $this->videoService->generateRawImage($mediaFile->path, min($validated['prev_time'], $mediaFile->mediable->duration-1));
            $mediaFile->preview_image_path = $this->imageService->generateImagePrev(MediaFolderTypes::VIDEOS, $mediaFile->base_name, $mediaFile->mediable->raw_image_path);
            $mediaFile->mediable->preview_video_path = $this->videoService->generatePrevVideo(
                $mediaFile->path,
                $mediaFile->mediable->width,
                $mediaFile->mediable->height,
                min($validated['prev_time'], $mediaFile->mediable->duration-1),  // duration-1秒の空き時間を作る→durationはround()で取った四捨五入の整数値なので、最小値でも実数値0.5秒の余裕ができる
                min(max($mediaFile->mediable->duration - $validated['prev_time'], 0.5), 3)    // 最大3秒、最小0.5秒
            );
        }
        DB::transaction(function () use ($mediaFile) {
            $mediaFile->save();
            if ($mediaFile->mediable->isDirty()) {
                $mediaFile->mediable->save();
            }
        });
    }
}
