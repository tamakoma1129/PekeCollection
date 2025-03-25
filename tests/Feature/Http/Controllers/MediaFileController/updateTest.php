<?php

use App\Models\MediaFile;

beforeEach(function () {
    login();
});

test('メディアファイルのtitleを変えられる', function () {
    $oldTitle = fake()->unique()->word();
    $newTitle = fake()->unique()->word();
    $mediaFile = MediaFile::factory()->create(["title" => $oldTitle]);

    $response = $this
        ->from(route('media.index', ['mediaType' => "all"]))
        ->patch(route("media_file.update", ["mediaFile" => $mediaFile]), ["title" => $newTitle]);

    $response->assertRedirect(route('media.index', ['mediaType' => "all"]))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseMissing("media_files", ["title" => $oldTitle]);
    $this->assertDatabaseHas("media_files", ["title" => $newTitle]);
});

test('ゲストはメディアファイルのtitleを変えられない', function () {
    auth()->logout();

    $oldTitle = fake()->unique()->word();
    $newTitle = fake()->unique()->word();
    $mediaFile = MediaFile::factory()->create(["title" => $oldTitle]);

    $response = $this
        ->from(route('media.index', ['mediaType' => "all"]))
        ->patch(route("media_file.update", ["mediaFile" => $mediaFile]), ["title" => $newTitle]);

    $response->assertRedirect(route("login"));

    $this->assertDatabaseHas("media_files", ["title" => $oldTitle]);
    $this->assertDatabaseMissing("media_files", ["title" => $newTitle]);
});

test('title変更のバリデーションエラーチェック', function () {
    $oldTitle = fake()->unique()->word();
    $newTitle = Str::random(256);
    $mediaFile = MediaFile::factory()->create(["title" => $oldTitle]);

    $response = $this
        ->from(route('media.index', ['mediaType' => "all"]))
        ->patch(route("media_file.update", ["mediaFile" => $mediaFile]), ["title" => $newTitle]);

    $response->assertRedirect(route('media.index', ['mediaType' => "all"]))
        ->assertInvalid([
           "title" => "titleは255文字以下である必要があります。",
        ]);

    $this->assertDatabaseHas("media_files", ["title" => $oldTitle]);
    $this->assertDatabaseMissing("media_files", ["title" => $newTitle]);
});

test('title変更のバリデーションパスチェック', function () {
    $oldTitle = fake()->unique()->word();
    $newTitle = Str::random(255);
    $mediaFile = MediaFile::factory()->create(["title" => $oldTitle]);

    $response = $this
        ->from(route('media.index', ['mediaType' => "all"]))
        ->patch(route("media_file.update", ["mediaFile" => $mediaFile]), ["title" => $newTitle]);

    $response->assertRedirect(route('media.index', ['mediaType' => "all"]))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseMissing("media_files", ["title" => $oldTitle]);
    $this->assertDatabaseHas("media_files", ["title" => $newTitle]);
});
