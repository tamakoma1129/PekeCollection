<?php

namespace App\UseCases\MediaFile;

use App\Models\Audio;
use App\Models\Image;
use App\Models\Manga;
use App\Models\MediaFile;
use App\Models\Video;

class IndexAction
{
    public function __invoke(?string $word, ?array $tags, ?string $orientation, string $mediaType)
    {
        $perPage = 30;
        $query = MediaFile::query()
            ->orderBy('created_at', 'desc')
            ->with(["mediable", "tags"]);

        if (!empty($word)) {
            $query->where(function ($query) use ($word) {
                $query->where('title', 'like', "%{$word}%");
            });
        }

        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $query->whereHas('tags', function ($query) use ($tag) {
                    $query->where('name', $tag);
                });
            }
        }


        if (!empty($orientation)) {
            $query->whereHasMorph(
                "mediable",
                [Image::class, Video::class, Manga::class],
                function ($query) use ($orientation) {
                    $query->whereColumn(
                        "width",
                        $orientation === "vertical" ? "<" : ">=",
                        "height"
                    );
                }
            );
        }

        switch ($mediaType) {
            case "all":
                break;
            case "image":
                $query->where(["mediable_type"=>Image::class]);
                break;
            case "video":
                $query->where(["mediable_type"=>Video::class]);
                break;
            case "audio":
                $query->where(["mediable_type"=>Audio::class]);
                break;
            case "manga":
                $query->where(["mediable_type"=>Manga::class]);
                break;
        }

        $medias = $query
            ->paginate($perPage)
            ->loadMorph("mediable", [
                Manga::class => ["pages"]
            ]);

        return $medias;
    }
}
