<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property \DateTime|null $created_at
 * @property \DateTime|null $updated_at
 */
class Tag extends Model
{
    use HasFactory;
    protected $fillable = ["id", "name"];

    public function mediaFiles()
    {
        return $this->belongsToMany(MediaFile::class, 'media_file_tag', 'tag_id', 'media_file_id');
    }
}
