<?php

namespace App\UseCases\MediaFile;

use App\Models\MediaFile;

class UpdateAction
{
    public function __invoke(MediaFile $mediaFile, array $validated)
    {
        $mediaFile->title = $validated["title"];
        $mediaFile->save();
    }
}
