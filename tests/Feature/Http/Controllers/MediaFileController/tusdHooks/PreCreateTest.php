<?php

use Symfony\Component\Mime\MimeTypes;

use function Pest\Laravel\postJson;

test('pre-createからのレスポンスが期待通り', function (string $mimeType) {
    Storage::fake('private');
    // ユーザー認証処理はweb&authミドルウェアを使っているため、簡易的にログインで確認している。
    // tus-jsを使って投稿できるかのテストはduskなどで行う。
    login();

    $fileName = Str::random(random_int(1, 128));
    $fileSize = random_int(1, 124 * 1024 * 1024 * 1024);  // 最大124GB
    $mimeTypes = new MimeTypes();
    $extension = $mimeTypes->getExtensions($mimeType)[0];

    $payload = createFromTusdPayload(
        "pre-create",
        $fileName,
        $mimeType,
        $fileSize,
    );

    $response = postJson(route("tusd-hooks"), $payload);

    $response->assertOk();

    $expectPath = "./uploads/";
    $majorType = explode("/", $mimeType)[0];
    if ($majorType === "image") {
        $expectPath .= "images/";
    } else {
        if ($majorType === "audio") {
            $expectPath .= "audios/";
        } else {
            if ($majorType === "video") {
                $expectPath .= "videos/";
            } else {
                if ($mimeType === "application/zip") {
                    $expectPath .= "mangas/";
                }
            }
        }
    }
    expect($response->json())->toMatchArray([
        "ChangeFileInfo" => [
            "Storage" => [
                "Path" => "$expectPath$fileName.$extension"
            ]
        ]
    ]);
})
    ->with("allMediaTypes");

test("ゲストだとpre-createできない", function () {
    Storage::fake('private');

    $this->assertGuest();
    $payload = createFromTusdPayload(
        "pre-create",
        "fileName.png",
        "image/png",
        10000
    );

    $response = postJson(route("tusd-hooks"), $payload);
    $response->assertStatus(401);
    expect($response->json())
        ->toMatchArray(
            ["message" => "Unauthenticated."]
        )
        ->not->toHaveKey("ChangeFileInfo");
});

test('pre-createの境界値バリデーションパスチェック', function (
    array $payload
) {
    Storage::fake('private');
    login();

    $response = postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    expect($response->json())
        ->not->toMatchArray(["RejectUpload" => true])
        ->toHaveKey("ChangeFileInfo");
})
    ->with([
        fn() => createFromTusdPayload(
            "pre-create",
            Str::random(124) . ".png",
            "image/png",
            10000
        ),
        fn() => createFromTusdPayload(
            "pre-create",
            "fileName.png",
            "image/png",
            124 * 1024 * 1024 * 1024,
        )
    ]);

test('pre-createのバリデーションエラーチェック', function (
    array $payload,
    string $expectedMessage
) {
    Storage::fake('private');
    login();

    $response = postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    expect($response->json())
        ->toMatchArray(["RejectUpload" => true])
        ->toMatchArray([
            "HTTPResponse" => [
                "StatusCode" => 422,
                "Body" => json_encode(["message" => $expectedMessage]),
                "Header" => [
                    "Content-Type" => "application/json",
                ]
            ]
        ]);
})
    ->with([
        [
            ["Type" => "pre-create"],
            "必須のフィールドがありません。"
        ],
        [
            fn() => createFromTusdPayload(
                "pre-create",
                Str::random(125) . ".png",
                "image/png",
                10000
            ),
            "ファイル名の長さは128文字までです。"
        ],
        [
            fn() => createFromTusdPayload(
                "pre-create",
                "filename.png",
                "image/png",
                124 * 1024 * 1024 * 1024 + 1,
            ),
            "アップロードできるファイルのサイズは124GBまでです。"
        ],
        [
            fn() => createFromTusdPayload(
                "pre-create",
                "filename.exe",
                "application/octet-stream",
                10000
            ),
            "このファイル形式には対応していません。"
        ],
        [
            fn() => createFromTusdPayload(
                "pre-create",
                "fakeImage.png",
                "image/fake",
                10000
            ),
            "このファイル形式には対応していません。"
        ]
    ]);

test('漫画以外のファイル名が重複したら、インクリメントしたパスを返す', function (
    string $mediaType,
    string $baseName,
    string $mimeType
) {
    Storage::fake('private');

    login();
    $payload = createFromTusdPayload(
        "pre-create",
        $baseName,
        $mimeType,
        10000,
    );

    Storage::disk("private")->put("./uploads/$mediaType/$baseName", "dummy");

    $response = postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $incrementFileName = pathinfo($baseName, PATHINFO_FILENAME)."(1).".pathinfo($baseName, PATHINFO_EXTENSION);
    expect($response->json())->toMatchArray([
        "ChangeFileInfo" => [
            "Storage" => [
                "Path" => "./uploads/$mediaType/$incrementFileName"
            ]
        ]
    ]);
})
    ->with([
        ["images", "image.png", "image/png"],
        ["audios", "audio.mp3", "audio/mpeg"],
        ["videos", "video.mp4", "video/mp4"],
    ]);

test('漫画以外のファイル名が複数個重複したら、インクリメントしたパスを返す', function (
    string $mediaType,
    string $baseName,
    string $mimeType
) {
    Storage::fake('private');

    login();
    $fileName = pathinfo($baseName, PATHINFO_FILENAME);
    $extension = pathinfo($baseName, PATHINFO_EXTENSION);
    // アップロードするファイル名自体はインクリメントしていないファイル名
    $payload = createFromTusdPayload(
        "pre-create",
        $baseName,
        $mimeType,
        10000,
    );

    $generateNum = random_int(1, 100);
    Storage::disk("private")->put("./uploads/$mediaType/$baseName", "dummy");
    for ($i = 1; $i <= $generateNum; $i++) {
        Storage::disk("private")->put("./uploads/$mediaType/$fileName($i).$extension", "dummy");
    }

    $response = postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $incrementNum = $generateNum + 1;
    $incrementFileName = $fileName."($incrementNum).$extension";
    expect($response->json())->toMatchArray([
        "ChangeFileInfo" => [
            "Storage" => [
                "Path" => "./uploads/$mediaType/$incrementFileName"
            ]
        ]
    ]);
})
    ->with([
        ["images", "image.png", "image/png"],
        ["audios", "audio.mp3", "audio/mpeg"],
        ["videos", "video.mp4", "video/mp4"],
    ]);

/**
 * 漫画の場合は、zipではなく展開後のフォルダ名が重複するかを見ないといけない
 */
test('漫画のファイル名が重複したら、インクリメントしたパスを返す', function () {
    Storage::fake('private');

    login();
    $fileName = Str::random(random_int(1, 100));
    $payload = createFromTusdPayload(
        "pre-create",
        "$fileName.zip",
        "application/zip",
        10000,
    );

    // 漫画の場合は、zipではなく展開後のフォルダ名が展開するかを見ないといけないので、ディレクトリを作成
    Storage::disk("private")->makeDirectory("./uploads/mangas/$fileName");

    $response = postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $incrementFileName = "$fileName(1)";
    expect($response->json())->toMatchArray([
        "ChangeFileInfo" => [
            "Storage" => [
                "Path" => "./uploads/mangas/$incrementFileName.zip"
            ]
        ]
    ]);
});

test('漫画のファイル名が複数個重複したら、インクリメントしたパスを返す', function () {
    Storage::fake('private');

    login();
    $fileName = Str::random(random_int(1, 100));
    // アップロードするファイル名自体はインクリメントしていないファイル名
    $payload = createFromTusdPayload(
        "pre-create",
        "$fileName.zip",
        "application/zip",
        10000,
    );

    $generateNum = random_int(1, 100);
    Storage::disk("private")->makeDirectory("./uploads/mangas/$fileName");
    for ($i = 1; $i <= $generateNum; $i++) {
        Storage::disk("private")->makeDirectory("./uploads/mangas/$fileName($i)");
    }

    $response = postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $incrementNum = $generateNum + 1;
    $incrementFileName = $fileName."($incrementNum)";
    expect($response->json())->toMatchArray([
        "ChangeFileInfo" => [
            "Storage" => [
                "Path" => "./uploads/mangas/$incrementFileName.zip"
            ]
        ]
    ]);
});

test('漫画の場合、事前にフォルダが作成される', function () {
    Storage::fake('private');

    login();
    $fileName = Str::random(random_int(1, 100));
    $baseName = "$fileName.zip";
    $expectFolderPath = "uploads/mangas/$fileName";
    $payload = createFromTusdPayload(
        "pre-create",
        $baseName,
        "application/zip",
        10000
    );
    $this->assertFalse(Storage::disk("private")->directoryExists($expectFolderPath));

    $response = postJson(route("tusd-hooks"), $payload);


    $response->assertOk();
    expect($response->json())->toMatchArray(
        [
            "ChangeFileInfo" => [
                "Storage" => [
                    "Path" => "./uploads/mangas/$fileName.zip"
                ]
            ]
        ]
    );
    $this->assertTrue(Storage::disk("private")->directoryExists($expectFolderPath));
});

test('漫画のファイル名が重複したら、事前作成フォルダがインクリメントする', function () {
    Storage::fake('private');

    login();
    $fileName = Str::random(random_int(1, 100));

    $generateNum = random_int(1, 100);
    Storage::disk("private")->makeDirectory("./uploads/mangas/$fileName");
    for ($i = 1; $i <= $generateNum; $i++) {
        Storage::disk("private")->makeDirectory("./uploads/mangas/$fileName($i)");
    }
    $payload = createFromTusdPayload(
        "pre-create",
        "$fileName.zip",
        "application/zip",
        10000
    );

    $response = postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $incrementNum = $generateNum + 1;
    $this->assertTrue(Storage::disk("private")->directoryExists("./uploads/mangas/$fileName($incrementNum)"));
});

test('漫画のファイル名が複数重複したら、事前作成フォルダがインクリメントする', function () {
    Storage::fake('private');

    login();
    $fileName = Str::random(random_int(1, 100));
    $baseName = "$fileName.zip";
    Storage::makeDirectory("uploads/mangas/$fileName");
    $payload = createFromTusdPayload(
        "pre-create",
        $baseName,
        "application/zip",
        10000
    );

    $response = postJson(route("tusd-hooks"), $payload);

    $response->assertOk();
    $this->assertTrue(Storage::disk("private")->directoryExists("./uploads/mangas/$fileName(1)"));
    $this->assertTrue(Storage::disk("private")->directoryExists("./uploads/mangas/$fileName"));
});
