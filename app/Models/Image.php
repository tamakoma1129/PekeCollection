<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property int $id
 * @property string $extension
 * @property string $dimensions
 * @property \DateTime|null $created_at
 * @property \DateTime|null $updated_at
 */
class Image extends Model
{
    use HasFactory;
    public function mediaFile(): morphOne
    {
        return $this->morphOne(MediaFile::class, 'mediable');
    }
}
