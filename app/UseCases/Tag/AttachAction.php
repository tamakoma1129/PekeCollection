<?php

namespace App\UseCases\Tag;

use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class AttachAction
{
    /**
     * @param int[] $media_ids
     * @param string[] $tags
     * @return void
     */
    public function __invoke(array $media_ids, array $tags)
    {
        DB::transaction(function () use ($media_ids, $tags) {
            $now = now();

            // 既存タグを取得
            $existingTags = Tag::whereIn('name', $tags)->get()->keyBy('name');

            // 新規タグを抽出
            $newTags = collect($tags)->diff($existingTags->keys())->map(function ($tag) use ($now) {
                return [
                    'name' => $tag,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            });

            // 新規タグがあればDBに作成
            if ($newTags->isNotEmpty()) {
                Tag::insert($newTags->all());
            }

            // 今回付与するタグIDを全て取得
            $tagIds = Tag::whereIn('name', $tags)->pluck('id')->all();

            $mediaTags = [];
            foreach ($media_ids as $media_id) {
                foreach ($tagIds as $tag_id) {
                    $mediaTags[] = [
                        'media_file_id' => $media_id,
                        'tag_id' => $tag_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // 重複エラーは無視し追加
            DB::table('media_file_tag')->insertOrIgnore($mediaTags);

            // タグのタイムスタンプを更新
            DB::table('tags')->whereIn('id', $tagIds)->update(['updated_at' => $now]);
        });
    }
}
