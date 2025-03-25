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
    $zipPath = createTestMangaZip([
        ["10x10", "png"],
        ["10x10", "png"],
        ["10x10", "png"],
    ]);
    Storage::disk("private")->put("uploads/mangas/manga.zip", file_get_contents($zipPath));

    $this->assertEquals(0, count(Storage::disk("private")->files("uploads/mangas/manga")));;

    $payload = postFinishPayload(
        "manga",
        "application/zip",
        10000,
        $this->path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $this->assertEquals(3, count(Storage::disk("private")->files("uploads/mangas/manga")));
});

test('漫画が投稿でき、infoファイルが削除される', function () {
    $zipPath = createTestMangaZip([
        ["10x10", "png"],
        ["10x10", "png"],
        ["10x10", "png"],
    ]);
    Storage::disk("private")->put("uploads/mangas/manga.zip", file_get_contents($zipPath));

    $this->assertFileExists(Storage::disk("private")->path($this->infoBaseName));

    $payload = postFinishPayload(
        "manga",
        "application/zip",
        10000,
        $this->path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();

    $this->assertFileDoesNotExist(Storage::disk("private")->path($this->infoBaseName));
});

test('漫画が投稿でき、zipファイルが削除される', function () {
    $zipPath = createTestMangaZip([
        ["10x10", "png"],
        ["10x10", "png"],
        ["10x10", "png"],
    ]);
    Storage::disk("private")->put("uploads/mangas/manga.zip", file_get_contents($zipPath));

    $this->assertFileExists(Storage::disk("private")->path("uploads/mangas/manga.zip"));

    $payload = postFinishPayload(
        "manga",
        "application/zip",
        10000,
        $this->path,
        $this->pathInfo
    );
    $response = $this->postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $this->assertFileDoesNotExist(Storage::disk("private")->path("uploads/mangas/manga.zip"));
});

test('漫画の拡張子を取得できる', function () {
    $zipPath = createTestMangaZip([
        ["10x10", "png"],
        ["10x10", "jpeg"],
        ["10x10", "gif"],
        ["10x10", "webp"],
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
    foreach (["png", "jpeg", "gif", "webp"] as $extension) {
        $this->assertDatabaseHas("manga_pages", ["file_extension" => $extension]);
    }
});

test('漫画投稿時プレビューが作成される', function () {
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
    $prevPath = "extras/mangas/manga/prev.webp";
    $this->assertDatabaseHas("media_files", ["preview_image_path" => $prevPath]);
    $this->assertFileExists(Storage::disk("private")->path($prevPath));
});

test('漫画投稿時軽量版も保存される', function () {
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
    for ($i = 1; $i <= 3; $i++) {
        $this->assertDatabaseHas("manga_pages", ["lite_path" => "extras/mangas/manga/$i.webp"]);
        $this->assertFileExists(Storage::disk("private")->path("extras/mangas/manga/$i.webp"));
    }
});

test('mangaに比率が最も多いディメンションが保存される', function () {
    $width = random_int(10,1000);
    $height = random_int(10,1000);
    $zipPath = createTestMangaZip([
        ["100x10", "png"],
        ["{$width}x$height", "png"],
        ["{$width}x$height", "png"],
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
    $this->assertDatabaseHas("mangas", [
        "width" => $width,
        "height" => $height,
    ]);
});

test('ファイル名が連番じゃないとエラー', function () {
    $zipPath = createTestMangaZip([
        ["100x10", "png", "fakeFileName"],
        ["100x10", "png"],
        ["100x10", "png"],
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

    $response->assertServerError();
    $this->assertDatabaseCount("mangas", 0);
    $this->assertDatabaseCount("manga_pages", 0);
    $this->assertDatabaseCount("media_files", 0);
    $this->assertFileDoesNotExist(Storage::disk("private")->path("uploads/mangas/manga.zip"));
    $this->assertDirectoryDoesNotExist(Storage::disk("private")->path("uploads/mangas/manga"));
    $this->assertDirectoryDoesNotExist(Storage::disk("private")->path("extras/mangas/manga"));
    $this->assertFileDoesNotExist(Storage::disk("private")->path($this->infoBaseName));
});
