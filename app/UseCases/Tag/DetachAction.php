<?php

namespace App\UseCases\Tag;

use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class DetachAction
{
    /**
     * @param int[] $media_ids
     * @param string[] $tags
     * @return void
     * @throws \Throwable
     */
    public function __invoke(array $media_ids, array $tags)
    {
        DB::transaction(function () use ($media_ids, $tags) {
            $tagIds = Tag::whereIn('name', $tags)->pluck('id')->all();

            DB::table('media_file_tag')
                ->whereIn('media_file_id', $media_ids)
                ->whereIn('tag_id', $tagIds)
                ->delete();

            // リレーションがないタグを削除
            Tag::doesntHave('mediaFiles')->delete();
        });
    }
}
