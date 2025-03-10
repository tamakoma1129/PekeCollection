<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $title
 * @property string $base_name
 * @property string $path
 * @property int $data_size
 * @property int $mediable_id
 * @property string $mediable_type
 * @property string $preview_image_path
 * @property \DateTime|null $created_at
 * @property \DateTime|null $updated_at
 */
class MediaFile extends Model
{
    use HasFactory;

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'media_file_tag', 'media_file_id', 'tag_id')
            ->withCount('mediaFiles')
            ->orderBy('media_files_count', 'desc');
    }

    public function createExtraPath(string $type, string $BaseName)
    {
        switch ($type) {
            case "App\Models\Image":
                $newExtraPath = "extras/images/{$BaseName}";
                break;
            case "App\Models\Video":
                $newExtraPath = "extras/videos/{$BaseName}";
                break;
            case "App\Models\Audio":
                $newExtraPath = "extras/audios/{$BaseName}";
                break;
            case "App\Models\Manga":
                $newExtraPath = "extras/mangas/{$BaseName}";
                break;
            default:
                throw new \Exception("メディアタイプがサポートされていません。");
        }

        return $newExtraPath;
    }
}
