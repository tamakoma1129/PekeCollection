<?php

use App\Models\MediaFile;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\delete;
use function Pest\Laravel\post;

test('ログインしていればimageが削除できる', function () {
    Storage::fake('private');
    $user = User::factory()->create(["password" => Hash::make("password")]);
    login($user);
    $imageFile = UploadedFile::fake()->image('image.png');
    post(route("image.store"), ["file" => $imageFile])->assertOk();

    assertDatabaseCount("images", 1);
    assertDatabaseCount("media_files", 1);
    Storage::disk("private")->assertExists("uploads/images/{$imageFile->getClientOriginalName()}");
    Storage::disk("private")->assertExists("extras/images/{$imageFile->getClientOriginalName()}/");

    $mediaFile = MediaFile::first();
    $response = delete(route("media_file.destroy", ["media_ids" => [$mediaFile->id], "password"=>"password"]));

    $response->assertRedirect();
    assertDatabaseCount("images", 0);
    assertDatabaseCount("media_files", 0);
    Storage::disk("private")->assertMissing("uploads/images/{$imageFile->getClientOriginalName()}");
    Storage::disk("private")->assertMissing("extras/images/{$imageFile->getClientOriginalName()}/");
});

test('ログインしていればaudioが削除できる', function () {
    Storage::fake('private');
    $user = User::factory()->create(["password" => Hash::make("password")]);
    login($user);
    $audioFile = new UploadedFile(
        "tests/Data/sample.mp3",
        "sample.mp3",
        "audio/mpeg",
        null,
        true
    );
    post(route('audio.store'), ["file"=>$audioFile]);

    assertDatabaseCount("audios", 1);
    assertDatabaseCount("media_files", 1);
    Storage::disk("private")->assertExists("uploads/audios/{$audioFile->getClientOriginalName()}");
    Storage::disk("private")->assertExists("extras/audios/{$audioFile->getClientOriginalName()}/");

    $mediaFile = MediaFile::first();
    $response = delete(route("media_file.destroy", ["media_ids" => [$mediaFile->id], "password"=>"password"]));

    $response->assertRedirect();
    assertDatabaseCount("audios", 0);
    assertDatabaseCount("media_files", 0);
    Storage::disk("private")->assertMissing("uploads/audios/{$audioFile->getClientOriginalName()}");
    Storage::disk("private")->assertMissing("extras/audios/{$audioFile->getClientOriginalName()}/");
});

test('ログインしていればvideoが削除できる', function () {
    Storage::fake('private');
    $user = User::factory()->create(["password" => Hash::make("password")]);
    login($user);
    $videoFile = new UploadedFile(
        "tests/Data/sample.mp4",
        "sample.mp4",
        "video/mp4",
        null,
        true
    );
    post(route('video.store'), ["file"=>$videoFile]);

    assertDatabaseCount("videos", 1);
    assertDatabaseCount("media_files", 1);
    Storage::disk("private")->assertExists("uploads/videos/{$videoFile->getClientOriginalName()}");
    Storage::disk("private")->assertExists("extras/videos/{$videoFile->getClientOriginalName()}/");

    $mediaFile = MediaFile::first();
    $response = delete(route("media_file.destroy", ["media_ids" => [$mediaFile->id], "password"=>"password"]));

    $response->assertRedirect();
    assertDatabaseCount("videos", 0);
    assertDatabaseCount("media_files", 0);
    Storage::disk("private")->assertMissing("uploads/videos/{$videoFile->getClientOriginalName()}");
    Storage::disk("private")->assertMissing("extras/videos/{$videoFile->getClientOriginalName()}/");
});

test('ログインしていればmangaが削除できる', function () {
    Storage::fake('private');
    $user = User::factory()->create(["password" => Hash::make("password")]);
    login($user);

    $title = "テスト漫画タイトル";
    $form = ["title"=>"$title","pages"=>[]];
    for ($i = 0; $i < 3; $i++) {
        $form["pages"][] = UploadedFile::fake()->image("page{$i}.jpg");
    }
    post(route('manga.store'), $form);
    assertDatabaseCount("manga_pages", 3);
    assertDatabaseCount("mangas", 1);
    assertDatabaseCount("media_files", 1);
    Storage::disk("private")->assertExists("uploads/mangas/{$title}/");
    Storage::disk("private")->assertExists("extras/mangas/{$title}/");

    $mediaFile = MediaFile::first();
    $response = delete(route("media_file.destroy", ["media_ids" => [$mediaFile->id], "password"=>"password"]));

    $response->assertRedirect();
    assertDatabaseCount("manga_pages", 0);
    assertDatabaseCount("mangas", 0);
    assertDatabaseCount("media_files", 0);
    Storage::disk("private")->assertMissing("uploads/mangas/{$title}/");
    Storage::disk("private")->assertMissing("extras/mangas/{$title}/");
});

test('非ログインだとファイルが削除できない', function () {
    Storage::fake('private');
    $user = User::factory()->create(["password" => Hash::make("password")]);
    $mediaFile = MediaFile::factory()->create();
    assertDatabaseCount("media_files", 1);
    assertDatabaseCount("images", 1);

    $response = delete(route("media_file.destroy", ["media_ids" => [$mediaFile->id], "password"=>"password"]));
    $response->assertRedirect("login");

    assertDatabaseCount("images", 1);
    assertDatabaseCount("media_files", 1);
});

test("複数削除できる", function () {
    Storage::fake('private');
    $user = User::factory()->create(["password" => Hash::make("password")]);
    login($user);

    $title = "テスト漫画タイトル";
    $form = ["title"=>"$title","pages"=>[]];
    for ($i = 0; $i < 3; $i++) {
        $form["pages"][] = UploadedFile::fake()->image("page{$i}.jpg");
    }
    post(route('manga.store'), $form);
    $imageFile = UploadedFile::fake()->image('image.png');
    post(route("image.store"), ["file" => $imageFile])->assertOk();

    assertDatabaseCount("images", 1);
    Storage::disk("private")->assertExists("uploads/images/{$imageFile->getClientOriginalName()}");
    Storage::disk("private")->assertExists("extras/images/{$imageFile->getClientOriginalName()}/");
    assertDatabaseCount("manga_pages", 3);
    assertDatabaseCount("mangas", 1);
    assertDatabaseCount("media_files", 2);
    Storage::disk("private")->assertExists("uploads/mangas/{$title}/");
    Storage::disk("private")->assertExists("extras/mangas/{$title}/");

    $mediaFiles = MediaFile::all();
    $response = delete(route("media_file.destroy", ["media_ids" => $mediaFiles->pluck("id")->all(), "password"=>"password"]));

    $response->assertRedirect();
    assertDatabaseCount("images", 0);
    Storage::disk("private")->assertMissing("uploads/images/{$imageFile->getClientOriginalName()}");
    Storage::disk("private")->assertMissing("extras/images/{$imageFile->getClientOriginalName()}/");
    assertDatabaseCount("manga_pages", 0);
    assertDatabaseCount("mangas", 0);
    assertDatabaseCount("media_files", 0);
    Storage::disk("private")->assertMissing("uploads/mangas/{$title}/");
    Storage::disk("private")->assertMissing("extras/mangas/{$title}/");
});

test('メディアを削除した後、使われているタグが0個のものがあったら削除', function () {
    Storage::fake('private');
    $user = User::factory()->create(["password" => Hash::make("password")]);
    login($user);

    $imageFile = UploadedFile::fake()->image('image.png');
    post(route("image.store"), ["file" => $imageFile])->assertOk();
    $mediaFile = MediaFile::first();
    $tag = Tag::factory()->create();
    $mediaFile->tags()->attach($tag);
    assertDatabaseCount("media_files", 1);
    assertDatabaseCount("tags", 1);
    assertDatabaseCount("media_file_tag", 1);

    $response = delete(route("media_file.destroy", ["media_ids" => [$mediaFile->id], "password"=>"password"]));
    $response->assertRedirect();

    assertDatabaseCount("media_files", 0);
    assertDatabaseCount("tags", 0);
    assertDatabaseCount("media_file_tag", 0);
});
