<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property int $id
 * @property string $title
 * @property int $width
 * @property int $height
 * @property \DateTime|null $created_at
 * @property \DateTime|null $updated_at
 */
class Manga extends Model
{
    public function mediaFile(): morphOne
    {
        return $this->morphOne(MediaFile::class, 'mediable');
    }

    public function pages()
    {
        return $this->hasMany(MangaPage::class)->orderBy('page_number');
    }
}
