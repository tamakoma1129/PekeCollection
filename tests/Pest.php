<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/


use App\Models\User;

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/


/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

// グローバル関数でloginを定義。　テスト時にしかこのPest.phpは読みこまれないので大丈夫っぽい。
function login($user = null)
{
    $user ??= User::factory()->create();
    test()->actingAs($user);
    return $user;
}

// tusdHookから送られるPayload
function preCreatePayload(
    string|Closure $fileName,
    string $mimeType,
    string $fileSize
) {
    return [
        'Type' => "pre-create",
        "Event" => [
            "Upload" => [
                "MetaData" => [
                    "filename" => $fileName,
                    "mimetype" => $mimeType
                ],
                "Size" => $fileSize
            ]
        ]
    ];
}

function postFinishPayload(
    string $fileName,
    string $mimeType,
    string $fileSize,
    string $path,
    string $infoPath
) {
    return [
        'Type' => "post-finish",
        "Event" => [
            "Upload" => [
                "MetaData" => [
                    "filename" => $fileName,
                    "mimetype" => $mimeType
                ],
                "Size" => $fileSize,
                "Storage" => [
                    "InfoPath" => $infoPath,
                    "Path" => $path
                ]
            ]
        ]
    ];
}

/**
 * 指定した仕様の画像ファイルをZIPにまとめて保存する関数。
 * 連番ファイル名 or 任意のファイル名を指定可能。
 *
 * @param array<array{0:string,1:string,2?:string}> $images
 *   例: [
 *     ['100x100', 'png'],             // → 自動的に "1.png"
 *     ['100x100', 'jpg', 'cover'],    // → "cover.jpg"
 *     ['200x300', 'png', 'back'],     // → "back.png"
 *   ]
 * @return string ZIPファイルの一時パス
 * @throws Exception
 */
function createTestMangaZip(array $images): string
{
    $tempZipPath = tempnam(sys_get_temp_dir(), 'test_manga') . '.zip';

    $zip = new ZipArchive();
    if ($zip->open($tempZipPath, ZipArchive::CREATE) !== true) {
        throw new \Exception("ZIPファイルを作成できませんでした: {$tempZipPath}");
    }

    $index = 1;
    foreach ($images as $imageData) {
        [$dimension, $extension, $filename] = array_pad($imageData, 3, null);

        [$width, $height] = explode('x', $dimension);

        if (!$filename) {
            $filename = (string)$index;
        }

        $img = imagecreatetruecolor((int)$width, (int)$height);

        ob_start();
        switch (strtolower($extension)) {
            case 'gif':
                imagegif($img);
            case 'jpg':
            case 'jpeg':
                imagejpeg($img);
                break;
            case 'webp':
                imagewebp($img);
            case 'png':
            default:
                imagepng($img);
                break;
        }
        $binaryImage = ob_get_clean();

        imagedestroy($img);

        $zip->addFromString("{$filename}.{$extension}", $binaryImage);

        $index++;
    }

    $zip->close();

    return $tempZipPath;
}


