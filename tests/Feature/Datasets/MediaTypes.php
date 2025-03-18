<?php
/**
 * アップロードに対応しているメディアタイプ一覧。
 * 参考元: https://www.iana.org/assignments/media-types/media-types.xhtml(2025-03-17)
 *
 * 参考元から主要だと判断したものだけを取得した。
 * 主要という判断は以下の元で行われた
 * 1. tamakomaの偏見
 * 2. ChatGPTの判断
 * 3. SymfonyのMimeTypesにあるgetExtensions関数で拡張子が取得できるもの
 *
 * また、これはアップロード時のバリデーションをパスする十分条件なだけで、他もパスする可能性がある。
 */

$imageMediaTypes = [
    ["image/apng"],
    ["image/avif"],
    ["image/bmp"],
    ["image/gif"],
    ["image/heic"],
    ["image/heic-sequence"],
    ["image/heif"],
    ["image/heif-sequence"],
    ["image/jpeg"],
    ["image/jp2"],
    ["image/jpx"],
    ["image/jxl"],
    ["image/png"],
    ["image/svg+xml"],
    ["image/tiff"],
    ["image/webp"],
];

$videoMediaTypes = [
    ["video/3gpp"],
    ["video/3gpp2"],
    ["video/H264"],
    ["video/jpeg"],
    ["video/mp4"],
    ["video/mpeg"],
    ["video/ogg"],
    ["video/quicktime"],
    ["video/webm"],
];

$audioMediaTypes = [
    ["audio/3gpp"],
    ["audio/3gpp2"],
    ["audio/aac"],
    ["audio/ac3"],
    ["audio/amr"],
    ["audio/flac"],
    ["audio/mp3"],
    ["audio/mp4"],
    ["audio/mpeg"],
    ["audio/ogg"],
    ["audio/wav"],
];

$mangaMediaTypes = [
    ["application/zip"],
];

dataset("imageMediaTypes", $imageMediaTypes);
dataset("videoMediaTypes", $videoMediaTypes);
dataset("audioMediaTypes", $audioMediaTypes);
dataset("mangaMediaTypes", $mangaMediaTypes);
dataset("allMediaTypes", array_merge($imageMediaTypes, $videoMediaTypes, $audioMediaTypes, $mangaMediaTypes));
