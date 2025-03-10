<?php

use Illuminate\Http\UploadedFile;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('ログイン時音源一覧が表示できる', function () {
    Storage::fake('private');
    login();

    $audioFileName = 'audio.mp3';
    $audioFile = new UploadedFile(
        "tests/Data/sample.mp3",
        $audioFileName,
        "audio/mpeg",
        null,
        true
    );
    $audioCoverFileName = 'audio_cover.mp3';
    $audioCoverFile = new UploadedFile(
        "tests/Data/sample_cover.mp3",
        "$audioCoverFileName",
        "audio/mpeg",
        null,
        true
    );

    post(route('audio.store'), ["file" => $audioFile])->assertOk();
    post(route('audio.store'), ["file" => $audioCoverFile])->assertOk();

    get(route("media.index", ["mediaType"=>"audio"]))
        ->assertOk()
        ->assertSee([$audioFileName, $audioCoverFileName]);
});

test('非ログイン時音源一覧が表示できない', function () {
    Storage::fake('private');

    $audioFileName = 'audio.mp3';
    $audioFile = new UploadedFile(
        "tests/Data/sample.mp3",
        $audioFileName,
        "audio/mpeg",
        null,
        true
    );

    post(route('audio.store'), ["file" => $audioFile])->assertRedirect(route("login"));

    get(route("media.index", ["mediaType"=>"audio"]))
        ->assertRedirect(route("login"))
        ->assertDontSee([$audioFileName]);
});
