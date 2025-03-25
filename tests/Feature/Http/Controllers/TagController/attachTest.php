<?php

use App\Models\MediaFile;
use App\Models\Tag;
use function Pest\Faker\fake;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

test('ログインしていればタグを付けられる', function () {
    login();
    $mediaFile = MediaFile::factory()->create();

    assertDatabaseCount("tags", 0);
    assertDatabaseCount("media_file_tag", 0);

    $tagName = "初音ミク";

    $form = ["media_ids"=>[$mediaFile->id],"tags"=>[$tagName]];
    $response = post(route("tag.attach"), $form);

    $response->assertRedirect();
    $tag = Tag::first();
    assertDatabaseHas("tags", ["name"=>$tagName]);
    assertDatabaseHas("media_file_tag", ["media_file_id"=>$mediaFile->id, "tag_id"=>$tag->id]);
});

test('非ログインではタグを付けられない', function () {
    $mediaFile = MediaFile::factory()->create();

    assertDatabaseCount("tags", 0);
    assertDatabaseCount("media_file_tag", 0);

    $tagName = "初音ミク";

    $form = ["media_ids"=>[$mediaFile->id],"tags"=>[$tagName]];
    $response = post(route("tag.attach"), $form);

    $response->assertRedirect(route("login"));
    $tag = Tag::first();
    assertDatabaseCount("tags", 0);
    assertDatabaseCount("media_file_tag", 0);
});

test('複数タグを複数のメディアに付けられる', function () {
    login();
    $mediaFiles = MediaFile::factory()->count(rand(2,10))->create();
    assertDatabaseCount("tags", 0);
    assertDatabaseCount("media_file_tag", 0);

    $mediaIds = $mediaFiles->pluck('id')->toArray();
    $tagNames = fake()->words(rand(1,10));
    $form = [
        'media_ids' => $mediaIds,
        'tags' => $tagNames,
    ];
    $response = post(route("tag.attach"), $form);

    $response->assertRedirect();
    foreach ($tagNames as $tagName) {
        assertDatabaseHas("tags", ["name" => $tagName]);
    }
    foreach ($mediaFiles as $mediaFile) {
        foreach ($tagNames as $tagName) {
            $tag = Tag::where('name', $tagName)->first();
            assertDatabaseHas("media_file_tag", [
                "media_file_id" => $mediaFile->id,
                "tag_id" => $tag->id,
            ]);
        }
    }
});

test('既に同じタグが付いていても問題無し', function () {
    login();
    $mediaFile = MediaFile::factory()->create();
    $mediaFile2 = MediaFile::factory()->create();
    $tag = Tag::factory()->create();
    $mediaFile->tags()->attach($tag);

    assertDatabaseCount("tags", 1);
    assertDatabaseCount("media_file_tag", 1);

    $form = ["media_ids"=>[$mediaFile->id, $mediaFile2->id],"tags"=>[$tag->name]];
    $response = post(route("tag.attach"), $form);

    $response->assertRedirect();
    assertDatabaseHas("tags", ["name"=>$tag->name]);
    assertDatabaseHas("media_file_tag", ["media_file_id"=>$mediaFile2->id, "tag_id"=>$tag->id]);
    assertDatabaseCount("tags", 1);
    assertDatabaseCount("media_file_tag", 2);
});

