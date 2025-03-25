<?php

use App\Models\MediaFile;
use App\Models\Tag;
use function Pest\Faker\fake;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;

test('ログインしていればタグを外せる', function () {
    login();
    $mediaFile = MediaFile::factory()->create();
    $tag = Tag::factory()->create();
    $mediaFile->tags()->attach($tag);

    assertDatabaseCount("tags", 1);
    assertDatabaseCount("media_file_tag", 1);

    $form = ["media_ids"=>[$mediaFile->id],"tags"=>[$tag->name]];
    $response = delete(route("tag.detach"), $form);

    $response->assertRedirect();
    assertDatabaseCount("tags", 0);
    assertDatabaseCount("media_file_tag", 0);
});

test('非ログインではタグを外せない', function () {
    $mediaFile = MediaFile::factory()->create();
    $tag = Tag::factory()->create();
    $mediaFile->tags()->attach($tag);

    assertDatabaseCount("tags", 1);
    assertDatabaseCount("media_file_tag", 1);

    $form = ["media_ids"=>[$mediaFile->id],"tags"=>[$tag->name]];
    $response = delete(route("tag.detach"), $form);

    $response->assertRedirect(route("login"));
    assertDatabaseCount("tags", 1);
    assertDatabaseCount("media_file_tag", 1);
});

test('複数タグを複数のメディアから外せる', function () {
    login();
    $mediaFiles = MediaFile::factory()->count(rand(2,10))->create();
    $tags = Tag::factory()->count(rand(2,10))->create();

    $tagIds = $tags->pluck('id')->toArray();
    foreach ($mediaFiles as $mediaFile) {
        $mediaFile->tags()->sync($tagIds);
    }
    assertDatabaseCount("tags", $tags->count());
    assertDatabaseCount("media_file_tag", $mediaFiles->count()*$tags->count());


    $form = [
        'media_ids' => $mediaFiles->pluck('id')->toArray(),
        'tags' => $tags->pluck("name")->toArray(),
    ];
    $response = delete(route("tag.detach"), $form);

    $response->assertRedirect();
    assertDatabaseCount("tags", 0);
    assertDatabaseCount("media_file_tag", 0);
});

test('既に同じタグが消えていても問題無し', function () {
    login();
    $mediaFile = MediaFile::factory()->create();
    $mediaFile2 = MediaFile::factory()->create();
    $tag = Tag::factory()->create();
    $mediaFile->tags()->attach($tag);

    assertDatabaseCount("tags", 1);
    assertDatabaseCount("media_file_tag", 1);

    $form = ["media_ids"=>[$mediaFile->id, $mediaFile2->id],"tags"=>[$tag->name]];
    $response = delete(route("tag.detach"), $form);

    $response->assertRedirect();
    assertDatabaseCount("tags", 0);
    assertDatabaseCount("media_file_tag", 0);
});

