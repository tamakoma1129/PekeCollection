<?php

use Illuminate\Http\UploadedFile;

test("privateに保存されている画像をgetできる", function () {
    Storage::fake('private');
    login();

    $path = "uploads/images/image.png";
    $image = UploadedFile::fake()->image('image.png');
    Storage::disk('private')->put($path, $image->get());

    $this->get("/private/{$path}")
        ->assertOk();
});

test("非ログイン時はprivateに保存されている画像をgetできない", function () {
    Storage::fake('private');
    $this->assertGuest();

    $path = "uploads/images/image.png";
    $image = UploadedFile::fake()->image('image.png');
    Storage::disk('private')->put($path, $image->get());

    $this->get("/private/{$path}")
        ->assertRedirect(route("login"));
});

test("privateのパスが間違っていたら404", function ($path) {
    Storage::fake('private');
    login();

    $this->get("/private/{$path}")
        ->assertNotFound();
})
    ->with([
        "",
        "uploads/images/wrongPathImage.png",
    ]);

test("音源・動画のテストも暇だったら作る")->todo();
