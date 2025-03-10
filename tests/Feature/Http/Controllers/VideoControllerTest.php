<?php

use Illuminate\Http\UploadedFile;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('ログイン時ビデオ一覧が表示できる', function () {
    Storage::fake('private');
    login();

    $videoFile = new UploadedFile(
        "tests/Data/sample.mp4",
        "sample.mp4",
        "video/mp4",
        null,
        true
    );

    post(route('video.store'), ["file" => $videoFile])->assertOk();

    get(route("media.index", ["mediaType"=>"video"]))
        ->assertOk()
        ->assertSee(["sample.mp4"]);
});

test('非ログイン時ビデオ一覧が表示できる', function () {
    Storage::fake('private');

    $videoFile = new UploadedFile(
        "tests/Data/sample.mp4",
        "sample.mp4",
        "video/mp4",
        null,
        true
    );

    post(route('video.store'), ["file" => $videoFile])->assertRedirect(route("login"));

    get(route("media.index", ["mediaType"=>"video"]))
        ->assertRedirect(route("login"))
        ->assertDontSee(["sample.mp4"]);
});
