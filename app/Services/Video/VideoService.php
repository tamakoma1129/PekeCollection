<?php

namespace App\Services\Video;

use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class VideoService
{
    /**
     * @param string $videoFilePath
     * @param string|null $disk
     * @return int
     */
    public function getDuration(string $videoFilePath, string $disk = "private"): int
    {
        return FFMpeg::fromDisk($disk)->open($videoFilePath)->getDurationInSeconds();
    }

    /**
     * @param string $videoFilePath
     * @param string|null $disk
     * @return array{width: int, height: int}
     */
    public function getResolution(string $videoFilePath, string $disk = "private")
    {
       $videoDimensions = FFmpeg::fromDisk($disk)->open($videoFilePath)
           ->getVideoStream()
           ->getDimensions();

       return [$videoDimensions->getWidth(), $videoDimensions->getHeight()];
    }

    public function generateRawImage(string $videoFilePath, float $frameSeconds = 0, string $disk = "private")
    {
        $fileName = pathinfo($videoFilePath, PATHINFO_BASENAME);
        $rawSavePath = "extras/videos/{$fileName}/raw.webp";

        FFMpeg::fromDisk($disk)
            ->open($videoFilePath)
            ->addFilter('-map', '0:v:0')
            ->addFilter('-vcodec', 'libwebp')
            ->getFrameFromSeconds($frameSeconds)
            ->export()
            ->save($rawSavePath);

        return $rawSavePath;
    }

    public function generatePrevVideo(string $videoFilePath, int $videoWidth, int $videoHeight, float $startSeconds = 0, float $durationSeconds = 3, string $disk = "private")
    {
        $fileName = pathinfo($videoFilePath, PATHINFO_BASENAME);
        $prevVideoPath = "extras/videos/{$fileName}/anime_prev.webp";

        $scale = $videoWidth>=$videoHeight ? "300:-1" : "-1:300";

        FFMpeg::fromDisk($disk)
            ->open($videoFilePath)
            ->export()
            ->toDisk($disk)
            ->addFilter([
                '-ss', $startSeconds,
                '-t', $durationSeconds,
                '-vf', 'fps=10,scale='.$scale.":flags=lanczos",
                '-loop', 0,
                '-vcodec', 'libwebp'
            ])
            ->save($prevVideoPath);

        return $prevVideoPath;
    }
}
