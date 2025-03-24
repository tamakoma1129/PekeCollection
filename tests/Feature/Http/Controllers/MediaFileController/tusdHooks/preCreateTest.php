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

test('漫画の場合、事前にフォルダが作成される', function () {
    Storage::fake('private');

    login();
    $fileName = "漫画.zip";
    $expectFolderPath = "uploads/mangas/漫画";
    $payload = createFromTusdPayload(
        "pre-create",
        $fileName,
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
                    "Path" => "./uploads/mangas/漫画.zip"
                ]
            ]
        ]
    );
    $this->assertTrue(Storage::disk("private")->directoryExists($expectFolderPath));
});

test('ファイル名が重複したら上書きせず、インクリメントする', function () {
});

