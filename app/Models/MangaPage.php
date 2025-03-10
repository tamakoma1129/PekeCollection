<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $manga_id
 * @property int $page_number
 * @property string $file_name
 * @property string $path
 * @property string $lite_path
 * @property string $file_extension
 * @property int $file_size
 * @property int $width
 * @property int $height
 * @property \DateTime|null $created_at
 * @property \DateTime|null $updated_at
 */
class MangaPage extends Model
{
    public function manga()
    {
        return $this->belongsTo(Manga::class);
    }
}
