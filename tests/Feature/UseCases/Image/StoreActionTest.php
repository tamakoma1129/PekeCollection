<?php

use App\Models\MediaFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

test('認証しているなら投稿できる', function () {
    Storage::fake('private');
    login();
    assertDatabaseCount("images", 0);
    assertDatabaseCount("media_files", 0);

    $imageFile = UploadedFile::fake()->image('image.png');
    $response = post(route('image.store'), ["file"=>$imageFile]);

    $response->assertOk();
    Storage::disk('private')->assertExists("uploads/images/{$imageFile->getClientOriginalName()}");
    assertDatabaseCount("images", 1);
    assertDatabaseCount("media_files", 1);
});

test('認証していないとログイン画面へ', function () {
    Storage::fake('private');
    assertDatabaseCount("images", 0);
    assertDatabaseCount("media_files", 0);

    $imageFile = UploadedFile::fake()->image('image.png');
    $response = post(route('image.store'), ["file"=>$imageFile]);

    assertDatabaseCount("images", 0);
    assertDatabaseCount("media_files", 0);
    $response->assertRedirect(route('login'));
});

test("imageの寸法を正しく取得できている", function (string $extension) {
    Storage::fake('private');
    login();

    $width = random_int(10, 2000);
    $height = random_int(10, 2000);
    $imageFile = UploadedFile::fake()->image('image.'.$extension, $width, $height);

    post(route('image.store'), ["file"=>$imageFile])
        ->assertOk();

    assertDatabaseHas("images",[
        "width"=>$width,
        "height"=>$height
        ]);

})->with("imagesExtensions");

test('ファイルが重複しても上書きせずインクリメントする', function (string $extension) {
    Storage::fake('private');
    login();
    $imageFileName = 'image.'.$extension;
    // ここでダミーファイルを用意して保存
    $dummyImage = UploadedFile::fake()->image($imageFileName);
    Storage::disk("private")->putFileAs("uploads/images", $dummyImage, $imageFileName);

    $imageFile = UploadedFile::fake()->image($imageFileName);
    post(route('image.store'), ["file" => $imageFile]);

    $expectFileName = 'image(1).'.$extension;
    Storage::disk("private")->assertExists("uploads/images/{$expectFileName}");
})->with("imagesExtensions");

test('画像投稿時プレビュー画像も作られる', function (string $extension) {
    Storage::fake('private');
    login();

    $width = random_int(10, 2000);
    $height = random_int(10, 2000);
    $imageFile = UploadedFile::fake()->image('image.'.$extension, $width, $height);

    post(route('image.store'), ["file"=>$imageFile])->assertOk();

    $expectPath = "extras/images/{$imageFile->getClientOriginalName()}/prev.webp";
    Storage::disk("private")->assertExists($expectPath);
    assertDatabaseHas("media_files", ["preview_image_path"=>$expectPath]);
})->with("imagesExtensions");

test("プレビュー画像が300px*300pxになる", function (string $extension) {
    Storage::fake('private');
    login();

    $beforeWidth = random_int(300, 2000);
    $beforeHeight = random_int(300, 2000);

    $imageFile = UploadedFile::fake()->image('image.'.$extension, $beforeWidth, $beforeHeight);

    post(route('image.store'), ["file"=>$imageFile])->assertOk();

    $mediaFile = MediaFile::first();
    $imagePath = Storage::disk("private")->path($mediaFile->preview_image_path);

    [$afterWidth, $afterHeight] = getimagesize($imagePath);

    expect($afterWidth)->toBe(300);
    expect($afterHeight)->toBe(300);
})->with("imagesExtensions");
