<?php

use App\Events\MediaProcessedEvent;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    Storage::fake('private');
    Event::fake();
    config(['queue.default' => 'sync']);
    login();

    $this->infoBaseName = Str::random(32) . "info";
    $this->pathInfo = "/private/$this->infoBaseName";
    Storage::disk("private")->put("./$this->infoBaseName", "dummy");
});

test('画像が投稿でき、dbに保存される', function () {
    $baseName = "image.png";
    $path = "/private/uploads/images/$baseName";

    $fakeImage = UploadedFile::fake()->image($baseName);
    Storage::disk('private')->put("uploads/images/$baseName", file_get_contents($fakeImage->getRealPath()));

    $payload = postFinishPayload(
        $baseName,
        "image/png",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();

    $this->assertDatabaseCount("images", 1);
    $this->assertDatabaseCount("media_files", 1);
});

test('ゲストは画像が投稿できない', function () {
    auth()->logout();
    $this->assertGuest();

    $baseName = "image.png";
    $path = "/private/uploads/images/$baseName";

    $fakeImage = UploadedFile::fake()->image($baseName);
    Storage::disk('private')->put("uploads/images/$baseName", file_get_contents($fakeImage->getRealPath()));

    $payload = postFinishPayload(
        $baseName,
        "image/png",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertUnauthorized();

    $this->assertDatabaseCount("audios", 0);
    $this->assertDatabaseCount("media_files", 0);
});

test('画像が投稿でき、infoファイルが削除される', function () {
    $baseName = "image.png";
    $path = "/private/uploads/images/$baseName";

    $fakeImage = UploadedFile::fake()->image($baseName);
    Storage::disk('private')->put("uploads/images/$baseName", file_get_contents($fakeImage->getRealPath()));

    $this->assertTrue(Storage::disk("private")->exists($this->infoBaseName));

    $payload = postFinishPayload(
        $baseName,
        "image/png",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();

    $this->assertFalse(Storage::disk("private")->exists($this->infoBaseName));
});

test('画像の拡張子を取得できる', function (
    string $extension,
    string $baseName,
) {
    $path = "/private/uploads/images/$baseName";

    $fakeImage = UploadedFile::fake()->image($baseName);
    Storage::disk('private')->put("uploads/images/$baseName", file_get_contents($fakeImage->getRealPath()));

    $this->assertTrue(Storage::disk("private")->exists($this->infoBaseName));

    $payload = postFinishPayload(
        $baseName,
        "image/png",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $this->assertDatabaseHas("images", [
        "extension" => $extension,
    ]);
})
    ->with([
        ["png", "image.png"],
        ["jpeg", "image.jpeg"],
        ["jpg", "image.jpg"],
        ["gif", "image.gif"],
        ["webp", "image.webp"],
        ["bmp", "image.bmp"],
    ]);

test('画像の寸法を正しく取得できている', function () {
    $baseName = "image.png";
    $path = "/private/uploads/images/$baseName";

    $width = random_int(10, 2000);
    $height = random_int(10, 2000);
    $fakeImage = UploadedFile::fake()->image($baseName, $width, $height);
    Storage::disk('private')->put("uploads/images/$baseName", file_get_contents($fakeImage->getRealPath()));

    $payload = postFinishPayload(
        $baseName,
        "image/png",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $this->assertDatabaseHas("images",[
        "width"=>$width,
        "height"=>$height
    ]);
});

test('画像投稿時プレビュー画像も作られる', function () {
    $baseName = "image.png";
    $path = "/private/uploads/images/$baseName";

    $width = random_int(10, 2000);
    $height = random_int(10, 2000);
    $fakeImage = UploadedFile::fake()->image($baseName, $width, $height);
    Storage::disk('private')->put("uploads/images/$baseName", file_get_contents($fakeImage->getRealPath()));

    $payload = postFinishPayload(
        $baseName,
        "image/png",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $prevPath = "extras/images/$baseName/prev.webp";
    Storage::disk("private")->assertExists($prevPath);
    $this->assertDatabaseHas("media_files", ["preview_image_path"=>$prevPath]);
});

test('画像が投稿後、イベントが発火する', function () {
    $baseName = "image.png";
    $path = "/private/uploads/images/$baseName";

    $fakeImage = UploadedFile::fake()->image($baseName);
    Storage::disk('private')->put("uploads/images/$baseName", file_get_contents($fakeImage->getRealPath()));

    $payload = postFinishPayload(
        $baseName,
        "image/png",
        10000,
        $path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();

    Event::assertDispatched(MediaProcessedEvent::class);
});
