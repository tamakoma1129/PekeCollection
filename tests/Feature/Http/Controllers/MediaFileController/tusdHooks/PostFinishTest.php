<?php


use App\Jobs\TusdHooks\PostFinish\ProcessAudio;
use App\Jobs\TusdHooks\PostFinish\ProcessImage;
use App\Jobs\TusdHooks\PostFinish\ProcessManga;
use App\Jobs\TusdHooks\PostFinish\ProcessVideo;

use function Pest\Laravel\postJson;

test('200レスポンスが帰ってきて、queueにジョブも入ってる', function (
    string $fileName,
    string $mimeType,
    string $type,
    string $jobClass
) {
    Queue::fake();

    login();
    $infoFileName = Str::random(32) . ".info";
    $payload = postFinishPayload(
        $fileName,
        $mimeType,
        10000,
        "/private/uploads/$type/$fileName",
        "/private/$infoFileName"
    );

    $response = postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    Queue::assertPushed($jobClass);
    Queue::assertCount(1);
})
    ->with([
        ["image.png", "image/png", "images", ProcessImage::class],
        ["audio.mp3", "audio/mpeg", "audios", ProcessAudio::class],
        ["video.mp4", "video/mp4", "videos", ProcessVideo::class],
        ["manga.zip", "application/zip", "mangas", ProcessManga::class],
    ]);

test('ゲストだと認証エラーかつqueueにジョブが入らない', function () {
    Queue::fake();

    $this->assertGuest();
    $infoFileName = Str::random(32) . ".info";
    $payload = postFinishPayload(
        "image.png",
        "image/png",
        10000,
        "/private/uploads/images/image.png",
        "/private/$infoFileName"
    );

    $response = postJson(route("tusd-hooks"), $payload);

    $response->assertUnauthorized();
    Queue::assertNothingPushed();
});

test('post-finishに不明なmimetypeが来たらエラー', function () {
    Queue::fake();

    login();
    $infoFileName = Str::random(32) . ".info";
    $payload = postFinishPayload(
        "image.png",
        "fake/fake",
        10000,
        "/private/uploads/images/image.png",
        "/private/$infoFileName"
    );

    $response = postJson(route("tusd-hooks"), $payload);

    $response->assertServerError();
    Queue::assertNothingPushed();
});

