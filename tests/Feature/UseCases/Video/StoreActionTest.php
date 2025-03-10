<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

test('認証しているなら投稿できる', function () {
    Storage::fake('private');
    login();
    assertDatabaseCount("videos", 0);
    assertDatabaseCount("media_files", 0);

    $videoFile = new UploadedFile(
        "tests/Data/sample.mp4",
        "sample.mp4",
        "video/mp4",
        null,
        true
    );
    $response = post(route('video.store'), ["file"=>$videoFile]);

    $response->assertOk();
    Storage::disk('private')->assertExists("uploads/videos/{$videoFile->getClientOriginalName()}");
    assertDatabaseCount("videos", 1);
    assertDatabaseCount("media_files", 1);
});

test('認証していないとログイン画面へ', function () {
    Storage::fake('private');
    assertDatabaseCount("videos", 0);
    assertDatabaseCount("media_files", 0);

    $videoFile = new UploadedFile(
        "tests/Data/sample.mp4",
        "sample.mp4",
        "video/mp4",
        null,
        true
    );
    $response = post(route('video.store'), ["file"=>$videoFile]);

    assertDatabaseCount("videos", 0);
    assertDatabaseCount("media_files", 0);
    $response->assertRedirect(route('login'));
});

test('videoの解像度と長さを正しく取得できている', function () {
    Storage::fake('private');
    login();

    $videoFile = new UploadedFile(
        "tests/Data/sample.mp4",
        "sample.mp4",
        "video/mp4",
        null,
        true
    );
    post(route('video.store'), ["file"=>$videoFile])->assertOk();

    assertDatabaseHas("videos", [
        "duration"=>6,
        "width"=>1920,
        "height"=>1080
    ]);
});

test('ファイルが重複しても上書きせずインクリメントする', function () {
    Storage::fake('private');
    login();

    $videoFileName = 'video.mp4';

    $videoFile = new UploadedFile(
        "tests/Data/sample.mp4",
        "$videoFileName",
        "video/mp4",
        null,
        true
    );

    // 直接保存
    Storage::disk("private")->putFileAs("uploads/videos", $videoFile, $videoFileName);
    // 同じファイル名をpost経由で保存
    post(route('video.store'), ["file" => $videoFile]);

    $expectFileNameArray = ['video.mp4', 'video(1).mp4'];
    foreach ($expectFileNameArray as $expectFileName) {
        Storage::disk("private")->assertExists("uploads/videos/{$expectFileName}");
    }
});

test("プレビュー画像が保存される", function () {
    Storage::fake('private');
    login();

    $videoFileName = 'video.mp4';

    $videoFile = new UploadedFile(
        "tests/Data/sample.mp4",
        "$videoFileName",
        "video/mp4",
        null,
        true
    );

    post(route('video.store'), ["file" => $videoFile]);

    assertDatabaseCount("videos", 1);
    assertDatabaseCount("media_files", 1);
    \Pest\Laravel\assertDatabaseMissing("media_files", ["preview_image_path"=>null]);

    Storage::disk("private")->assertExists("extras/videos/{$videoFileName}/prev.webp");
});

test("raw_imageが保存される", function () {
    Storage::fake('private');
    login();

    $videoFileName = 'video.mp4';

    $videoFile = new UploadedFile(
        "tests/Data/sample.mp4",
        "$videoFileName",
        "video/mp4",
        null,
        true
    );

    post(route('video.store'), ["file" => $videoFile]);

    assertDatabaseCount("videos", 1);
    assertDatabaseCount("media_files", 1);
    \Pest\Laravel\assertDatabaseMissing("videos", ["raw_image_path"=>null]);

    Storage::disk("private")->assertExists("extras/videos/{$videoFileName}/raw.webp");

    $imagePath = Storage::disk("private")->path("extras/videos/{$videoFileName}/raw.webp");
    [$width, $height] = getimagesize($imagePath);
    // 縦横が圧縮されていない
    expect($width)->toBe(1920);
    expect($height)->toBe(1080);
});

test("動画のアニメーションプレビュー画像が保存される", function () {
    Storage::fake('private');
    login();

    $videoFileName = 'video.mp4';

    $videoFile = new UploadedFile(
        "tests/Data/sample.mp4",
        "$videoFileName",
        "video/mp4",
        null,
        true
    );

    post(route('video.store'), ["file" => $videoFile]);

    Storage::disk("private")->assertExists("extras/videos/{$videoFileName}/anime_prev.webp");
});
