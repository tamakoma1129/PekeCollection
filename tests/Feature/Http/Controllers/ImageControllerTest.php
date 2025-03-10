<?php

use Illuminate\Http\UploadedFile;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('ログイン時画像一覧が表示できる', function (string $extension) {
    Storage::fake('private');
    login();

    $imageFile = UploadedFile::fake()->image('image.'.$extension, random_int(300, 2000), random_int(300, 2000));

    post(route('image.store'), ["file"=>$imageFile])->assertOk();

    get(route("media.index", ["mediaType"=>"image"]))
        ->assertOk()
        ->assertSee([$imageFile->getClientOriginalName()]);
})->with("imagesExtensions");

test('非ログイン時画像一覧が表示できる', function (string $extension) {
    Storage::fake('private');

    $imageFile = UploadedFile::fake()->image('image.'.$extension, random_int(300, 2000), random_int(300, 2000));

    post(route('image.store'), ["file"=>$imageFile])->assertRedirect("login");

    get(route("media.index", ["mediaType"=>"image"]))
        ->assertRedirect("login")
        ->assertDontSee([$imageFile->getClientOriginalName()]);
})->with("imagesExtensions");
