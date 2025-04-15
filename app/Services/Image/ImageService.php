<?php

namespace App\Services\Image;

use App\Enums\MediaFolderTypes;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;


class ImageService
{
    /**
     * @param string $path
     * @param string|null $readDisk
     * @return array
     */
    public function getDimensions(string $path, string $readDisk="private"): array
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read(Storage::disk($readDisk)->path($path));

        return [$image->width(), $image->height()];
    }

    /**
     * @param MediaFolderTypes $types
     * @param string $fileName
     * @param string $readPath
     * @param string|null $readDisk
     * @return string
     * @throws FileNotFoundException
     */
    public function generateImagePrev(MediaFolderTypes $types, string $fileName, string $readPath, string $readDisk="private"): string
    {
        $savePath = "extras/{$types->value}/{$fileName}/prev.webp";

        $this->generateLiteImage($readPath, $savePath, readDisk: $readDisk);

        return $savePath;
    }

    /**
     * 画像比率はそのままで、画質を下げwebp変換するもの。
     * 画像の一時置き換えや、プレビュー用。
     *
     * @param string $readPath
     * @param string $savePath
     * @param string|null $readDisk
     * @param string|null $saveDisk
     * @return void
     */
    public function generateLiteImage(
        string $readPath,
        string $savePath,
        string $readDisk="private",
        string $saveDisk="private"
    ): void
    {
        if (!Storage::disk($readDisk)->exists($readPath)) {
            throw new \Exception("disk:{$readDisk} でファイルが見つかりません path:{$readPath}");
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->read(Storage::disk($readDisk)->path($readPath));
        $quality = 70;

        $pointer = $image
                    ->scaleDown(400, 400)
                    ->toWebp($quality)
                    ->toFilePointer();

        // LaravelのStorage::diskならフォルダが存在しないとき作ってくれるので。
        Storage::disk($saveDisk)->put($savePath, $pointer);
    }
}
