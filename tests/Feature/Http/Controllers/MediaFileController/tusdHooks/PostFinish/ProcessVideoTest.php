<?php

use App\Events\MediaProcessedEvent;

beforeEach(function () {
    Storage::fake('private');
    Event::fake();
    config(['queue.default' => 'sync']);
    login();

    $this->infoBaseName = Str::random(32) . "info";
    $this->pathInfo = "/private/$this->infoBaseName";
    Storage::disk("private")->put("./$this->infoBaseName", "dummy");
});

test('動画が投稿でき、dbに保存される', function () {
    $baseName = "video.mp4";
    $path = "/private/uploads/videos/$baseName";
    Storage::disk('private')->put("uploads/videos/$baseName", file_get_contents(base_path("tests/Data/sample.mp4")));

    $payload = postFinishPayload(
        $baseName,
        "video/mp4",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();

    $this->assertDatabaseCount("videos", 1);
    $this->assertDatabaseCount("media_files", 1);
});

test('ゲストは動画が投稿できない', function () {
    auth()->logout();

    $baseName = "video.mp4";
    $path = "/private/uploads/videos/$baseName";
    Storage::disk('private')->put("uploads/videos/$baseName", file_get_contents(base_path("tests/Data/sample.mp4")));

    $payload = postFinishPayload(
        $baseName,
        "video/mp4",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertUnauthorized();

    $this->assertDatabaseCount("videos", 0);
    $this->assertDatabaseCount("media_files", 0);
});

test('動画が投稿でき、infoファイルが削除される', function () {
    $baseName = "video.mp4";
    $path = "/private/uploads/videos/$baseName";
    Storage::disk('private')->put("uploads/videos/$baseName", file_get_contents(base_path("tests/Data/sample.mp4")));

    $this->assertFileExists(Storage::disk("private")->path($this->infoBaseName));

    $payload = postFinishPayload(
        $baseName,
        "video/mp4",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $this->assertFileDoesNotExist(Storage::disk("private")->path($this->infoBaseName));
});

test('動画の拡張子を取得できる', function () {
    $baseName = "video.mp4";
    $path = "/private/uploads/videos/$baseName";
    Storage::disk('private')->put("uploads/videos/$baseName", file_get_contents(base_path("tests/Data/sample.mp4")));

    $payload = postFinishPayload(
        $baseName,
        "video/mp4",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();

    $this->assertDatabaseHas("videos", [
        "extension" => "mp4",
    ]);
});

test('動画の長さを取得できる', function () {
    $baseName = "video.mp4";
    $path = "/private/uploads/videos/$baseName";
    Storage::disk('private')->put("uploads/videos/$baseName", file_get_contents(base_path("tests/Data/sample.mp4")));

    $payload = postFinishPayload(
        $baseName,
        "video/mp4",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();

    $this->assertDatabaseHas("videos", [
        "duration" => 6,
    ]);
});

test('動画の解像度を取得できる', function () {
    $baseName = "video.mp4";
    $path = "/private/uploads/videos/$baseName";
    Storage::disk('private')->put("uploads/videos/$baseName", file_get_contents(base_path("tests/Data/sample.mp4")));

    $payload = postFinishPayload(
        $baseName,
        "video/mp4",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();

    $this->assertDatabaseHas("videos", [
        "width" => 1920,
        "height" => 1080,
    ]);
});

test('動画の動画プレビューが作成される', function () {
    $baseName = "video.mp4";
    $path = "/private/uploads/videos/$baseName";
    Storage::disk('private')->put("uploads/videos/$baseName", file_get_contents(base_path("tests/Data/sample.mp4")));

    $payload = postFinishPayload(
        $baseName,
        "video/mp4",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();

    $videoPrevPath = "extras/videos/video.mp4/anime_prev.webp";
    $this->assertDatabaseHas("videos", [
        "preview_video_path" => $videoPrevPath,
    ]);
    $this->assertFileExists(Storage::disk("private")->path($videoPrevPath));
});

test('動画の画像プレビューが作成される', function () {
    $baseName = "video.mp4";
    $path = "/private/uploads/videos/$baseName";
    Storage::disk('private')->put("uploads/videos/$baseName", file_get_contents(base_path("tests/Data/sample.mp4")));

    $payload = postFinishPayload(
        $baseName,
        "video/mp4",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();

    $imagePrevPath = "extras/videos/video.mp4/prev.webp";
    $this->assertDatabaseHas("media_files", [
        "preview_image_path" => $imagePrevPath,
    ]);
    $this->assertFileExists(Storage::disk("private")->path($imagePrevPath));
});

test('動画のプレビューの無加工版が作成される', function () {
    $baseName = "video.mp4";
    $path = "/private/uploads/videos/$baseName";
    Storage::disk('private')->put("uploads/videos/$baseName", file_get_contents(base_path("tests/Data/sample.mp4")));

    $payload = postFinishPayload(
        $baseName,
        "video/mp4",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();

    $rawPrevPath = "extras/videos/video.mp4/raw.webp";
    $this->assertDatabaseHas("videos", [
        "raw_image_path" => $rawPrevPath,
    ]);
    $this->assertFileExists(Storage::disk("private")->path($rawPrevPath));
});

test('動画が投稿後、イベントが発火する', function () {
    $baseName = "video.mp4";
    $path = "/private/uploads/videos/$baseName";
    Storage::disk('private')->put("uploads/videos/$baseName", file_get_contents(base_path("tests/Data/sample.mp4")));

    $payload = postFinishPayload(
        $baseName,
        "video/mp4",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();

    Event::assertDispatched(MediaProcessedEvent::class);
});
