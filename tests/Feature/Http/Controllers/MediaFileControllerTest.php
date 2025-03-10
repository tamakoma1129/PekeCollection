<?php


use Illuminate\Http\UploadedFile;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('ログイン時メディア一覧が表示できる', function () {
    Storage::fake('private');
    login();

    $videoFile = new UploadedFile(
        "tests/Data/sample.mp4",
        "sample.mp4",
        "video/mp4",
        null,
        true
    );
    $imageFile = UploadedFile::fake()->image('image.png', random_int(300, 2000), random_int(300, 2000));
    $audioCoverFile = new UploadedFile(
        "tests/Data/sample_cover.mp3",
        "audio_cover.mp3",
        "audio/mpeg",
        null,
        true
    );

    post(route('video.store'), ["file" => $videoFile])->assertOk();
    post(route('image.store'), ["file" => $imageFile])->assertOk();
    post(route('audio.store'), ["file" => $audioCoverFile])->assertOk();

    get(route("media.index", ["mediaType"=>"all"]))
        ->assertOk()
        ->assertSee(["sample.mp4","audio_cover.mp3","image.png"]);
});

test('非ログイン時メディア一覧が表示できる', function () {
    Storage::fake('private');

    $videoFile = new UploadedFile(
        "tests/Data/sample.mp4",
        "sample.mp4",
        "video/mp4",
        null,
        true
    );
    $imageFile = UploadedFile::fake()->image('image.png', random_int(300, 2000), random_int(300, 2000));
    $audioCoverFile = new UploadedFile(
        "tests/Data/sample_cover.mp3",
        "audio_cover.mp3",
        "audio/mpeg",
        null,
        true
    );

    post(route('video.store'), ["file" => $videoFile])->assertRedirect(route("login"));
    post(route('image.store'), ["file" => $imageFile])->assertRedirect(route("login"));
    post(route('audio.store'), ["file" => $audioCoverFile])->assertRedirect(route("login"));

    get(route("media.index", ["mediaType"=>"all"]))
        ->assertRedirect(route("login"))
        ->assertDontSee(["sample.mp4","audio_cover.mp3","image.png"]);
});
