<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

test('認証しているなら投稿できる', function () {
    Storage::fake('private');
    login();
    assertDatabaseCount("manga_pages", 0);
    assertDatabaseCount("mangas", 0);
    assertDatabaseCount("media_files", 0);

    $title = "テスト漫画タイトル";

    $form = ["title"=>$title,"pages"=>[]];
    for ($i = 0; $i < 25; $i++) {
        $form["pages"][] = UploadedFile::fake()->image("page{$i}.jpg");
    }
    $response = post(route('manga.store'), $form);

    $response->assertRedirect(route('manga.create'));
    Storage::disk('private')->assertExists("uploads/mangas/{$title}/");
    assertDatabaseCount("manga_pages", 25);
    assertDatabaseCount("mangas", 1);
    assertDatabaseCount("media_files", 1);
});

test('認証していないとログイン画面へ', function () {
    Storage::fake('private');
    assertDatabaseCount("manga_pages", 0);
    assertDatabaseCount("mangas", 0);
    assertDatabaseCount("media_files", 0);

    $title = "テスト漫画タイトル";

    $form = ["title"=>$title,"pages"=>[]];
    for ($i = 0; $i < 25; $i++) {
        $form["pages"][] = UploadedFile::fake()->image("page{$i}.jpg");
    }
    $response = post(route('manga.store'), $form);

    $response->assertRedirect(route('login'));
    Storage::disk('private')->assertMissing("uploads/mangas/{$title}/");
    assertDatabaseCount("manga_pages", 0);
    assertDatabaseCount("mangas", 0);
    assertDatabaseCount("media_files", 0);
});

test('ファイルが重複しても上書きせずインクリメントする', function () {
    Storage::fake('private');
    login();

    $mangaName = "ドラえもん";
    // ここでダミーフォルダを保存
    Storage::disk("private")->makeDirectory("uploads/mangas/{$mangaName}");

    $form = ["title"=>$mangaName, "pages"=>[]];
    $form["pages"][] = UploadedFile::fake()->image($mangaName.".jpg");
    post(route('manga.store'), $form)->assertRedirect(route('manga.create'));

    $expectFolderName = "{$mangaName}(1)";
    Storage::disk("private")->assertExists("uploads/mangas/{$expectFolderName}/");
});

test('漫画投稿時プレビュー画像も作られる', function () {
    Storage::fake('private');
    login();

    $mangaName = "ドラえもん";
    $form = ["title"=>$mangaName, "pages"=>[]];
    $form["pages"][] = UploadedFile::fake()->image($mangaName.".jpg");

    post(route('manga.store'), $form)->assertRedirect(route('manga.create'));

    $expectPath = "extras/mangas/{$mangaName}/prev.webp";
    Storage::disk("private")->assertExists($expectPath);
    assertDatabaseHas("media_files", ["preview_image_path"=>$expectPath]);
});

test('漫画投稿時webpに変換される', function () {
    Storage::fake('private');
    login();

    $mangaName = "ドラえもん";
    $form = ["title"=>$mangaName, "pages"=>[]];
    $form["pages"][] = UploadedFile::fake()->image($mangaName.".jpg");
    $form["pages"][] = UploadedFile::fake()->image($mangaName.".png");
    $form["pages"][] = UploadedFile::fake()->image($mangaName.".gif");

    post(route('manga.store'), $form)->assertRedirect(route('manga.create'));

    for ($i = 1; $i < 4; $i++) {
        $expectPath = "uploads/mangas/{$mangaName}/00{$i}.webp";
        Storage::disk("private")->assertExists($expectPath);
    }
});

test('widthとheightがmangaにも保存される', function () {
    Storage::fake('private');
    login();

    $title = "テスト漫画タイトル";

    $form = ["title"=>$title,"pages"=>[]];
    // 1ページ目だけサイズが違う
    $form["pages"][] = UploadedFile::fake()->image("0.jpg", 10, 100);
    for ($i = 0; $i < 5; $i++) {
        $form["pages"][] = UploadedFile::fake()->image("page{$i}.jpg", 500, 800);
    }
    $response = post(route('manga.store'), $form);

    $response->assertRedirect(route('manga.create'));
    // pageにwidthとheightがある
    assertDatabaseHas("manga_pages", ["file_name"=>"001.webp", "width"=>10, "height"=>100]);
    for ($i = 2; $i < 7; $i++) {
        assertDatabaseHas("manga_pages", ["file_name"=>"00{$i}.webp", "width"=>500, "height"=>800]);
    }
    // mangaは最も多いwidthとheightの組み合わせを採用
    assertDatabaseHas("mangas", ["width"=>500, "height"=>800]);
});
