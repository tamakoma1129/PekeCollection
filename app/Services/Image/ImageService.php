<?php

namespace App\Services\Image;

use App\Enums\MediaFolderTypes;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;


class ImageService
{
    public function getDimensions(string $path, ?string $readDisk="private")
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
    public function generateImagePrev(MediaFolderTypes $types, string $fileName, string $readPath, ?string $readDisk="private"): string
    {
        $savePath = "extras/{$types->value}/{$fileName}/prev.webp";

        if (!Storage::disk($readDisk)->exists($readPath)) {
            throw new FileNotFoundException("disk:{$readDisk} でファイルが見つかりません path:{$readPath}");
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->read(Storage::disk($readDisk)->path($readPath));

        $width = $image->width();
        $height = $image->height();

        if ($width >= $height) {
            // 画面上部中心で一対一比率で切り抜く。pixivの方式を参考にした(人物写真は中央上部にフォーカスが当たりやすいっぽい)。
            $image->crop($height, $height, round(($width/2)-($height/2)), 0);
        } else {
            $image->crop($width, $width, 0, 0);
        }

        $image->resizeDown(300,300);

        $pointer = $image->toWebp()->toFilePointer();
        Storage::disk($readDisk)->put($savePath, $pointer);

        return $savePath;
    }

    /**
     * こちらのgenerateThumbnailは画像を一時的に置き換えるときに使う軽量画像を作成するもの。
     * 上のgenerateImagePrevは、index画面で映す軽量画像を作成するもの。
     *
     * 後々統合する予定だが、一旦別々で作成している。
     *
     * @param string $readPath
     * @param string $savePath
     * @param string|null $readDisk
     * @param string|null $saveDisk
     * @return void
     */
    public function generateThumbnail(
        string $readPath,
        string $savePath,
        ?string $readDisk="private",
        ?string $saveDisk="private"
    ): void
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read(Storage::disk($readDisk)->path($readPath));
        $quality = 70;

        $image
            ->scaleDown(400, 400)
            ->toWebp($quality)
            ->save(Storage::disk($saveDisk)->path($savePath));
    }
}
