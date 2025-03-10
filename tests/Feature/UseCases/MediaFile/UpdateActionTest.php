<?php

use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

test('ログインしていればimageのtitleが変更できる', function () {
    Storage::fake('private');
    login();
    $oldTitle = fake()->word;
    $oldBaseName = "{$oldTitle}.jpg";
    $newTitle = fake()->word;
    $newBaseName = "{$newTitle}.jpg";
    $imageFile = UploadedFile::fake()->image($oldBaseName);
    post(route("image.store"), ["file" => $imageFile]);

    assertDatabaseHas("media_files", [
        "title"=>$oldTitle,
        "base_name"=>$oldBaseName,
        "path"=>"uploads/images/{$oldBaseName}",
        "preview_image_path"=>"extras/images/{$oldBaseName}/prev.webp"
    ]);
    assertDatabaseCount("images", 1);
    Storage::disk("private")->assertExists("uploads/images/{$oldBaseName}");
    Storage::disk("private")->assertExists("extras/images/{$oldBaseName}/");

    $mediaFile = MediaFile::first();
    $response = patch(route("media_file.update", $mediaFile->id), ["title"=>$newTitle]);

    $response->assertRedirect();
    assertDatabaseHas("media_files", [
        "title"=>$newTitle,
        "base_name"=>$newBaseName,
        "path"=>"uploads/images/{$newBaseName}",
        "preview_image_path"=>"extras/images/{$newBaseName}/prev.webp",
        ]);
    Storage::disk("private")->assertMissing("uploads/images/{$oldBaseName}");
    Storage::disk("private")->assertMissing("extras/images/{$oldBaseName}/");
    Storage::disk("private")->assertExists("uploads/images/{$newBaseName}");
    Storage::disk("private")->assertExists("extras/images/{$newBaseName}/");
});

test('ログインしていればaudioのtitleが変更できる', function () {
    Storage::fake('private');
    login();
    $oldTitle = "sample_cover";
    $oldBaseName = "{$oldTitle}.mp3";
    $newTitle = fake()->word;
    $newBaseName = "{$newTitle}.mp3";
    $audioFile = new UploadedFile(
        "tests/Data/sample_cover.mp3",
        "sample_cover.mp3",
        "audio/mpeg",
        null,
        true
    );
    post(route("audio.store"), ["file" => $audioFile]);

    assertDatabaseHas("media_files", [
        "title"=>$oldTitle,
        "base_name"=>$oldBaseName,
        "path"=>"uploads/audios/{$oldBaseName}",
        "preview_image_path"=>"extras/audios/{$oldBaseName}/prev.webp"
    ]);
    assertDatabaseCount("audios", 1);
    Storage::disk("private")->assertExists("uploads/audios/{$oldBaseName}");
    Storage::disk("private")->assertExists("extras/audios/{$oldBaseName}/");

    $mediaFile = MediaFile::first();
    $response = patch(route("media_file.update", $mediaFile->id), ["title"=>$newTitle]);

    $response->assertRedirect();
    assertDatabaseHas("media_files", [
        "title"=>$newTitle,
        "base_name"=>$newBaseName,
        "path"=>"uploads/audios/{$newBaseName}",
        "preview_image_path"=>"extras/audios/{$newBaseName}/prev.webp",
    ]);
    Storage::disk("private")->assertMissing("uploads/audios/{$oldBaseName}");
    Storage::disk("private")->assertMissing("extras/audios/{$oldBaseName}/");
    Storage::disk("private")->assertExists("uploads/audios/{$newBaseName}");
    Storage::disk("private")->assertExists("extras/audios/{$newBaseName}/");
});

test('ログインしていればvideoのtitleが変更できる', function () {
    Storage::fake('private');
    login();
    $oldTitle = "sample";
    $oldBaseName = "{$oldTitle}.mp4";
    $newTitle = fake()->word;
    $newBaseName = "{$newTitle}.mp4";
    $videoFile = new UploadedFile(
        "tests/Data/sample.mp4",
        "sample.mp4",
        "video/mp4",
        null,
        true
    );
    post(route('video.store'), ["file"=>$videoFile]);

    assertDatabaseHas("media_files", [
        "title"=>$oldTitle,
        "base_name"=>$oldBaseName,
        "path"=>"uploads/videos/{$oldBaseName}",
        "preview_image_path"=>"extras/videos/{$oldBaseName}/prev.webp"
    ]);
    assertDatabaseCount("videos", 1);
    Storage::disk("private")->assertExists("uploads/videos/{$oldBaseName}");
    Storage::disk("private")->assertExists("extras/videos/{$oldBaseName}/");

    $mediaFile = MediaFile::first();
    $response = patch(route("media_file.update", $mediaFile->id), ["title"=>$newTitle]);

    $response->assertRedirect();
    assertDatabaseHas("media_files", [
        "title"=>$newTitle,
        "base_name"=>$newBaseName,
        "path"=>"uploads/videos/{$newBaseName}",
        "preview_image_path"=>"extras/videos/{$newBaseName}/prev.webp",
    ]);
    Storage::disk("private")->assertMissing("uploads/videos/{$oldBaseName}");
    Storage::disk("private")->assertMissing("extras/videos/{$oldBaseName}/");
    Storage::disk("private")->assertExists("uploads/videos/{$newBaseName}");
    Storage::disk("private")->assertExists("extras/videos/{$newBaseName}/");
});

test('ログインしていればmangaのtitleが変更できる', function () {
    Storage::fake('private');
    login();
    $oldTitle = fake()->word;
    $oldBaseName = "{$oldTitle}";
    $newTitle = fake()->word;
    $newBaseName = "{$newTitle}";
    $form = ["title"=>$oldTitle,"pages"=>[]];
    for ($i = 0; $i < 25; $i++) {
        $form["pages"][] = UploadedFile::fake()->image("page{$i}.jpg");
    }
    $response = post(route('manga.store'), $form);

    assertDatabaseHas("media_files", [
        "title"=>$oldTitle,
        "base_name"=>$oldBaseName,
        "path"=>"uploads/mangas/{$oldBaseName}/",
        "preview_image_path"=>"extras/mangas/{$oldBaseName}/prev.webp"
    ]);
    assertDatabaseCount("mangas", 1);
    Storage::disk("private")->assertExists("uploads/mangas/{$oldBaseName}");
    Storage::disk("private")->assertExists("extras/mangas/{$oldBaseName}/");

    $mediaFile = MediaFile::first();
    $response = patch(route("media_file.update", $mediaFile->id), ["title"=>$newTitle]);

    $response->assertRedirect();
    assertDatabaseHas("media_files", [
        "title"=>$newTitle,
        "base_name"=>$newBaseName,
        "path"=>"uploads/mangas/{$newBaseName}",
        "preview_image_path"=>"extras/mangas/{$newBaseName}/prev.webp",
    ]);
    Storage::disk("private")->assertMissing("uploads/mangas/{$oldBaseName}");
    Storage::disk("private")->assertMissing("extras/mangas/{$oldBaseName}/");
    Storage::disk("private")->assertExists("uploads/mangas/{$newBaseName}");
    Storage::disk("private")->assertExists("extras/mangas/{$newBaseName}/");
});


test('非ログインだとtitleが変更できない', function () {
    Storage::fake('private');

    $oldTitle = fake()->word;
    $mediaFile = MediaFile::factory()->create(["title"=>$oldTitle]);
    $response = patch(route("media_file.update", $mediaFile->id), ["title"=>"新しいタイトル"]);

    $response->assertRedirect(route("login"));
    assertDatabaseHas("media_files", ["title"=>$oldTitle,]);
});



test('変更後のファイル名が被ってたらインクリメントする', function () {
    Storage::fake('private');
    login();
    $oldTitle = fake()->word;
    $oldBaseName = "{$oldTitle}.jpg";
    $newTitle = fake()->word;
    $newBaseName = "{$newTitle}.jpg";
    $imageFile = UploadedFile::fake()->image($oldBaseName);
    post(route("image.store"), ["file" => $imageFile]);

    // 変更後の名前で保存しておく
    $dummyImage = UploadedFile::fake()->image($newBaseName);
    Storage::disk("private")->putFileAs("uploads/images", $dummyImage, $newBaseName);
    $expectTitle = "{$newTitle}";   // titleは被ってもいいのでやりたいものに
    $expectBaseName = "{$newTitle}(1).jpg";

    assertDatabaseHas("media_files", [
        "title"=>$oldTitle,
        "base_name"=>$oldBaseName,
        "path"=>"uploads/images/{$oldBaseName}",
        "preview_image_path"=>"extras/images/{$oldBaseName}/prev.webp"
    ]);
    assertDatabaseCount("images", 1);
    Storage::disk("private")->assertExists("uploads/images/{$oldBaseName}");
    Storage::disk("private")->assertExists("extras/images/{$oldBaseName}/");

    $mediaFile = MediaFile::first();
    $response = patch(route("media_file.update", $mediaFile->id), ["title"=>$newTitle]);

    $response->assertRedirect();
    assertDatabaseHas("media_files", [
        "title"=>$expectTitle,
        "base_name"=>$expectBaseName,
        "path"=>"uploads/images/{$expectBaseName}",
        "preview_image_path"=>"extras/images/{$expectBaseName}/prev.webp",
    ]);
    Storage::disk("private")->assertExists("uploads/images/{$newBaseName}");
    Storage::disk("private")->assertMissing("extras/images/{$newBaseName}/");
    Storage::disk("private")->assertExists("uploads/images/{$expectBaseName}");
    Storage::disk("private")->assertExists("extras/images/{$expectBaseName}/");
});

test('変更後のフォルダ名が被ってたらインクリメントする', function () {
    Storage::fake('private');
    login();
    $oldTitle = fake()->word;
    $oldBaseName = "{$oldTitle}";
    $newTitle = fake()->word;
    $newBaseName = "{$newTitle}";
    $form = ["title"=>$oldTitle,"pages"=>[]];
    for ($i = 0; $i < 25; $i++) {
        $form["pages"][] = UploadedFile::fake()->image("page{$i}.jpg");
    }
    $response = post(route('manga.store'), $form);

    // 変更後の名前で保存しておく
    Storage::disk("private")->makeDirectory("uploads/mangas/{$newBaseName}");
    $expectTitle = "{$newTitle}";   // titleは被ってもいいのでやりたいものに
    $expectBaseName = "{$newTitle}(1)";

    assertDatabaseHas("media_files", [
        "title"=>$oldTitle,
        "base_name"=>$oldBaseName,
        "path"=>"uploads/mangas/{$oldBaseName}/",
        "preview_image_path"=>"extras/mangas/{$oldBaseName}/prev.webp"
    ]);
    assertDatabaseCount("mangas", 1);
    Storage::disk("private")->assertExists("uploads/mangas/{$oldBaseName}");
    Storage::disk("private")->assertExists("extras/mangas/{$oldBaseName}/");

    $mediaFile = MediaFile::first();
    $response = patch(route("media_file.update", $mediaFile->id), ["title"=>$newTitle]);

    $response->assertRedirect();
    assertDatabaseHas("media_files", [
        "title"=>$expectTitle,
        "base_name"=>$expectBaseName,
        "path"=>"uploads/mangas/{$expectBaseName}",
        "preview_image_path"=>"extras/mangas/{$expectBaseName}/prev.webp",
    ]);
    Storage::disk("private")->assertExists("uploads/mangas/{$newBaseName}");
    Storage::disk("private")->assertMissing("extras/mangas/{$newBaseName}/");
    Storage::disk("private")->assertExists("uploads/mangas/{$expectBaseName}");
    Storage::disk("private")->assertExists("extras/mangas/{$expectBaseName}/");
});
