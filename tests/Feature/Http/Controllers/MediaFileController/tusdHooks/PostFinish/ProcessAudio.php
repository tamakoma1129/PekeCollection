<?php

beforeEach(function () {
    Storage::fake('private');
    config(['queue.default' => 'sync']);
    login();

    $this->infoBaseName = Str::random(32) . "info";
    $this->pathInfo = "/private/$this->infoBaseName";
    Storage::disk("private")->put("./$this->infoBaseName", "dummy");
});

test('音源が投稿でき、dbに保存される', function () {
    $baseName = "audio.mp3";
    $path = "/private/uploads/audios/$baseName";
    Storage::disk('private')->put("uploads/audios/$baseName", file_get_contents(base_path("tests/Data/sample.mp3")));

    $payload = postFinishPayload(
        $baseName,
        "audio/mpeg",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();

    $this->assertDatabaseCount("audios", 1);
    $this->assertDatabaseCount("media_files", 1);
});

test('音源が投稿でき、infoファイルが削除される', function () {
    $baseName = "audio.mp3";
    $path = "/private/uploads/audios/$baseName";
    Storage::disk('private')->put("uploads/audios/$baseName", file_get_contents(base_path("tests/Data/sample.mp3")));
    $this->assertTrue(Storage::disk("private")->exists($this->infoBaseName));

    $payload = postFinishPayload(
        $baseName,
        "audio/mpeg",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $this->assertFalse(Storage::disk("private")->exists($this->infoBaseName));
});

test('音源の拡張子を取得できる', function (
    string $extension,
    string $fileName
) {
    $baseName = "audio.$extension";
    $path = "/private/uploads/audios/$baseName";
    Storage::disk('private')->put("uploads/audios/$baseName", file_get_contents(base_path("tests/Data/$fileName")));

    $payload = postFinishPayload(
        $baseName,
        "audio/mpeg",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $this->assertDatabaseHas("audios", [
        "extension" => $extension,
    ]);
})
    ->with([
        ["mp3", "sample.mp3"],
        ["wav", "sample.wav"],
        ["flac", "sample.flac"],
    ]);

test('ゲストは音源が投稿できない', function () {
    auth()->logout();

    $this->assertGuest();
    $baseName = "audio.mp3";
    $path = "/private/uploads/audios/$baseName";
    Storage::disk('private')->put("uploads/audios/$baseName", file_get_contents(base_path("tests/Data/sample.mp3")));

    $payload = postFinishPayload(
        $baseName,
        "audio/mpeg",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertUnauthorized();

    $this->assertDatabaseCount("audios", 0);
    $this->assertDatabaseCount("media_files", 0);
});

test('audioの長さを取得できる', function () {
    $baseName = "audio.mp3";
    $path = "/private/uploads/audios/$baseName";
    Storage::disk('private')->put("uploads/audios/$baseName", file_get_contents(base_path("tests/Data/sample.mp3")));

    $payload = postFinishPayload(
        $baseName,
        "audio/mpeg",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $this->assertDatabaseHas("audios", [
        "duration" => 8,
    ]);
});

test("カバー画像があればプレビューとして保存される", function () {
    $baseName = "cover.mp3";
    $path = "/private/uploads/audios/$baseName";
    Storage::disk('private')->put(
        "uploads/audios/$baseName",
        file_get_contents(base_path("tests/Data/sample_cover.mp3"))
    );

    $payload = postFinishPayload(
        $baseName,
        "audio/mpeg",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $prevPath = "extras/audios/$baseName/prev.webp";
    $this->assertDatabaseHas("media_files", [
        "preview_image_path" => $prevPath,
    ]);
    $this->assertTrue(Storage::disk("private")->exists($prevPath));
});

test("カバー画像があればraw_imageとしても保存される", function () {
    $baseName = "cover.mp3";
    $path = "/private/uploads/audios/$baseName";
    Storage::disk('private')->put(
        "uploads/audios/$baseName",
        file_get_contents(base_path("tests/Data/sample_cover.mp3"))
    );

    $payload = postFinishPayload(
        $baseName,
        "audio/mpeg",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $rawPath = "extras/audios/{$baseName}/raw.webp";
    $this->assertDatabaseHas("audios", [
        "raw_image_path" => $rawPath,
    ]);
    $this->assertTrue(Storage::disk("private")->exists($rawPath));

    $imagePath = Storage::disk("private")->path($rawPath);

    [$width, $height] = getimagesize($imagePath);
    // 縦横が圧縮されていない
    expect($width)->toBe(400);
    expect($height)->toBe(400);
});

test("カバーが無ければ何も保存されない", function () {
    $baseName = "audio.mp3";
    $path = "/private/uploads/audios/$baseName";
    Storage::disk('private')->put("uploads/audios/$baseName", file_get_contents(base_path("tests/Data/sample.mp3")));

    $payload = postFinishPayload(
        $baseName,
        "audio/mpeg",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $rawPath = "extras/audios/{$baseName}/raw.webp";
    $prevPath = "extras/audios/{$baseName}/prev.webp";
    $this->assertDatabaseHas("audios", ["raw_image_path" => null]);
    $this->assertDatabaseHas("media_files", ["preview_image_path" => null]);
    $this->assertFalse(Storage::disk("private")->exists($prevPath));
    $this->assertFalse(Storage::disk("private")->exists($rawPath));
});

test("音源のプレビューが保存される", function (
    string $fileName
) {
    $baseName = "audio.mp3";
    $path = "/private/uploads/audios/$baseName";
    Storage::disk('private')->put("uploads/audios/$baseName", file_get_contents(base_path("tests/Data/$fileName")));

    $payload = postFinishPayload(
        $baseName,
        "audio/mpeg",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $prevPath = "extras/audios/$baseName/prev.mp3";
    $this->assertDatabaseHas("audios", [
        "preview_audio_path" => $prevPath,
    ]);
    $this->assertTrue(Storage::disk("private")->exists($prevPath));
})
    ->with([
        "sample.mp3",
        "soundless.mp3" // 無音でも処理が実行されることのテスト
    ]);

