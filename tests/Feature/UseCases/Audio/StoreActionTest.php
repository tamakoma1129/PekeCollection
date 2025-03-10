<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

test('認証しているなら投稿できる', function () {
    Storage::fake('private');
    login();
    assertDatabaseCount("audios", 0);
    assertDatabaseCount("media_files", 0);

    $audioFile = new UploadedFile(
        "tests/Data/sample.mp3",
        "sample.mp3",
        "audio/mpeg",
        null,
        true
    );
    $response = post(route('audio.store'), ["file"=>$audioFile]);

    $response->assertOk();
    Storage::disk('private')->assertExists("uploads/audios/{$audioFile->getClientOriginalName()}");
    assertDatabaseCount("audios", 1);
    assertDatabaseCount("media_files", 1);
});

test('認証していないとログイン画面へ', function () {
    Storage::fake('private');
    assertDatabaseCount("audios", 0);
    assertDatabaseCount("media_files", 0);

    $audioFile = new UploadedFile(
        "tests/Data/sample.mp3",
        "sample.mp3",
        "audio/mpeg",
        null,
        true
    );
    $response = post(route('audio.store'), ["file"=>$audioFile]);

    assertDatabaseCount("audios", 0);
    assertDatabaseCount("media_files", 0);
    $response->assertRedirect(route('login'));
});

test('audioの長さを正しく取得できている', function () {
    Storage::fake('private');
    login();

    $audioFile = new UploadedFile(
        "tests/Data/sample.mp3",
        "sample.mp3",
        "audio/mpeg",
        null,
        true
    );
    post(route('audio.store'), ["file"=>$audioFile])->assertOk();

    assertDatabaseHas("audios", [
        "duration"=>8,
    ]);
});

test('ファイルが重複しても上書きせずインクリメントする', function () {
    Storage::fake('private');
    login();

    $audioFileName = 'audio.mp3';

    $audioFile = new UploadedFile(
        "tests/Data/sample.mp3",
        "$audioFileName",
        "audio/mpeg",
        null,
        true
    );

    // 直接保存
    Storage::disk("private")->putFileAs("uploads/audios", $audioFile, $audioFileName);
    // 同じファイル名をpost経由で保存
    post(route('audio.store'), ["file" => $audioFile]);

    $expectFileNameArray = ['audio.mp3', 'audio(1).mp3'];
    foreach ($expectFileNameArray as $expectFileName) {
        Storage::disk("private")->assertExists("uploads/audios/{$expectFileName}");
    }
});

test("カバー画像があればプレビューとして保存される", function () {
    Storage::fake('private');
    login();

    assertDatabaseCount("audios", 0);
    assertDatabaseCount("media_files", 0);

    $audioFileName = 'cover_audio.mp3';

    $audioFile = new UploadedFile(
        "tests/Data/sample_cover.mp3",
        "$audioFileName",
        "audio/mpeg",
        null,
        true
    );

    post(route('audio.store'), ["file" => $audioFile]);

    assertDatabaseCount("audios", 1);
    assertDatabaseCount("media_files", 1);
    \Pest\Laravel\assertDatabaseMissing("media_files", ["preview_image_path"=>null]);

    Storage::disk("private")->assertExists("extras/audios/{$audioFileName}/prev.webp");
});

test("カバー画像raw_imageでも保存される", function () {
    Storage::fake('private');
    login();

    assertDatabaseCount("audios", 0);
    assertDatabaseCount("media_files", 0);

    $audioFileName = 'cover_audio.mp3';

    $audioFile = new UploadedFile(
        "tests/Data/sample_cover.mp3",
        "$audioFileName",
        "audio/mpeg",
        null,
        true
    );

    post(route('audio.store'), ["file" => $audioFile]);

    assertDatabaseCount("audios", 1);
    assertDatabaseCount("media_files", 1);
    \Pest\Laravel\assertDatabaseMissing("audios", ["raw_image_path"=>null]);

    Storage::disk("private")->assertExists("extras/audios/{$audioFileName}/raw.webp");

    $imagePath = Storage::disk("private")->path("extras/audios/{$audioFileName}/raw.webp");

    [$width, $height] = getimagesize($imagePath);
    // 縦横が圧縮されていない
    expect($width)->toBe(400);
    expect($height)->toBe(400);
});

test("カバーが無ければ何も保存されない", function () {
    Storage::fake('private');
    login();

    $audioFileName = 'audio.mp3';

    $audioFile = new UploadedFile(
        "tests/Data/sample.mp3",
        "$audioFileName",
        "audio/mpeg",
        null,
        true
    );

    post(route('audio.store'), ["file" => $audioFile]);

    assertDatabaseCount("audios", 1);
    assertDatabaseCount("media_files", 1);
    assertDatabaseHas("audios", ["raw_image_path"=>null]);
    assertDatabaseHas("media_files", ["preview_image_path"=>null]);
});

test("音源のプレビューが保存される", function () {
    Storage::fake('private');
    login();

    $audioFileName = 'audio.mp3';

    $audioFile = new UploadedFile(
        "tests/Data/sample.mp3",
        "$audioFileName",
        "audio/mpeg",
        null,
        true
    );

    post(route('audio.store'), ["file" => $audioFile]);

    Storage::disk("private")->assertExists("extras/audios/{$audioFileName}/prev.mp3");
});

test("無音でもプレビューが保存される", function () {
    Storage::fake('private');
    login();

    $audioFileName = 'soundless.mp3';

    $audioFile = new UploadedFile(
        "tests/Data/soundless.mp3",
        "$audioFileName",
        "audio/mpeg",
        null,
        true
    );

    post(route('audio.store'), ["file" => $audioFile]);

    Storage::disk("private")->assertExists("extras/audios/{$audioFileName}/prev.mp3");
});
