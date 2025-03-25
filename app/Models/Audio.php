<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property int $id
 * @property int $media_file_id
 * @property string $extension
 * @property int $duration
 * @property string $raw_image_path
 * @property string $preview_audio_path
 * @property \DateTime|null $created_at
 * @property \DateTime|null $updated_at
 */
class Audio extends Model
{
    use HasFactory;
    protected $table = 'audios';

    public function mediaFile(): morphOne
    {
        return $this->morphOne(MediaFile::class, 'mediable');
    }
}
