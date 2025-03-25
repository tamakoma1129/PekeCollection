<?php


beforeEach(function () {
    Storage::fake('private');
    config(['queue.default' => 'sync']);
    login();

    $this->infoBaseName = Str::random(32) . "info";
    $this->pathInfo = "/private/$this->infoBaseName";
    Storage::disk("private")->put("./$this->infoBaseName", "dummy");

    // pre-createと同じように事前フォルダを作成しておく
    Storage::disk("private")->makeDirectory("uploads/mangas/manga");
    $this->path = "/private/uploads/mangas/manga.zip";
});



test('漫画が投稿でき、dbに保存される', function () {
    $zipPath = createTestMangaZip([
        ["10x10", "png"],
        ["10x10", "png"],
        ["10x10", "png"],
    ]);
    Storage::disk("private")->put("uploads/mangas/manga.zip", file_get_contents($zipPath));

    $payload = postFinishPayload(
        "manga",
        "application/zip",
        10000,
        $this->path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();

    $this->assertDatabaseCount("mangas", 1);
    $this->assertDatabaseCount("manga_pages", 3);
    $this->assertDatabaseCount("media_files", 1);
});

test('ゲストは漫画が投稿できない', function () {
    auth()->logout();

    $zipPath = createTestMangaZip([
        ["10x10", "png"],
        ["10x10", "png"],
        ["10x10", "png"],
    ]);
    Storage::disk("private")->put("uploads/mangas/manga.zip", file_get_contents($zipPath));

    $payload = postFinishPayload(
        "manga",
        "application/zip",
        10000,
        $this->path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertUnauthorized();

    $this->assertDatabaseCount("mangas", 0);
    $this->assertDatabaseCount("manga_pages", 0);
    $this->assertDatabaseCount("media_files", 0);
});


test('zipを展開できる', function () {

});

test('漫画が投稿でき、infoファイルが削除される', function () {

});

test('漫画が投稿でき、zipファイルが削除される', function () {

});

test('漫画の拡張子を取得できる', function () {

});

test('漫画投稿時プレビュー画像も作られる', function () {

});

test('漫画投稿時軽量版も保存される', function () {

});

test('mangaに比率が最も多いディメンションが保存される', function () {

});

test('ファイル名が連番じゃないとエラー', function () {

});
