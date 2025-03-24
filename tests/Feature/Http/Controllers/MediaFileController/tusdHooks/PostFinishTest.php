<?php


use function Pest\Laravel\postJson;

test('200レスポンスが帰ってきて、queueにジョブも入ってる', function () {
    Queue::fake();

    login();
    $infoFileName = Str::random(32) . ".info";
    $payload = postFinishPayload(
        "image.png",
        "image/png",
        10000,
        "/private/uploads/images/image.png",
        "/private/$infoFileName"
    );

    $response = postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    Queue::assertCount(1);
});

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

