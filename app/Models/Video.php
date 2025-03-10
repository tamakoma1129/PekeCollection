<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property int $id
 * @property string $extension
 * @property int $duration
 * @property int $resolution_width
 * @property int $resolution_height
 * @property string $raw_image_path
 * @property string $preview_video_path
 * @property \DateTime|null $created_at
 * @property \DateTime|null $updated_at
 */
class Video extends Model
{
    public function mediaFile(): morphOne
    {
        return $this->morphOne(MediaFile::class, 'mediable');
    }
}
