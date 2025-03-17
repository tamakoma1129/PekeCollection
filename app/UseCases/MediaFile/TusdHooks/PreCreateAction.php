<?php

namespace App\UseCases\MediaFile\TusdHooks;

use App\Http\Requests\MediaFile\TusdHookRequest;
use App\Services\File\FileService;
use Symfony\Component\Mime\MimeTypes;

class PreCreateAction
{
    protected FileService $fileService;
    public function __construct(FileService $fileService){
        $this->fileService = $fileService;
    }

    private const MAX_FILENAME_LENGTH = 128;
    private const MAX_FILE_SIZE_GB = 124;
    private const MAX_FILE_SIZE_BYTES = self::MAX_FILE_SIZE_GB * 1024 * 1024 * 1024;

    private const ALLOWED_MIME_MAJOR_TYPES = [
        "image",
        "audio",
        "video",
    ];
    private const ALLOWED_MIME_TYPES = [
        // 漫画
        "application/zip",
    ];

    public function __invoke(TusdHookRequest $request)
    {
        $fileName = $request->input("Event.Upload.MetaData.filename");
        $mimeType = $request->input("Event.Upload.MetaData.mimetype");
        $fileSize = $request->input("Event.Upload.Size");

        if (!$fileName || !$mimeType || !$fileSize) {
            return $this->rejectUpload(422, "必須のフィールドがありません。");
        }
        if (!$this->isValidFilename($fileName)) {
            return $this->rejectUpload(422, "ファイル名の長さは".self::MAX_FILENAME_LENGTH."文字までです。");
        }
        if (!$this->isValidFileSize($fileSize)) {
            return $this->rejectUpload(422, "アップロードできるファイルのサイズは". self::MAX_FILE_SIZE_GB ."GBまでです。");
        }
        if (!$this->isValidFileType($mimeType)) {
            return $this->rejectUpload(422, "このファイル形式には対応していません。");
        }

        // 漫画の場合は先に一意のフォルダ名を作り、そのフォルダ名で作っておく。
        if ($mimeType === "application/zip") {
            $folderPath = $this->generateUniqueFolderPath($fileName);
            $this->fileService->makeDirectory($folderPath);
        }

        $path = $this->generateUniquePath($fileName, $mimeType);

        return response()->json([
            "status" => 200,
            "ChangeFileInfo" => [
                "Storage" => [
                    "Path" => $path,
                ]
            ],
        ]);
    }

    private function isValidFilename(string $filename): bool
    {
        return strlen($filename) <= self::MAX_FILENAME_LENGTH;
    }

    private function isValidFileSize(int $filesize): bool
    {
        return $filesize <= self::MAX_FILE_SIZE_BYTES;
    }

    private function isValidFileType(string $filetype): bool
    {
        $majorType = explode( "/", $filetype)[0];
        // メジャータイプがALLOWED_MIME_MAJOR_TYPESに含まれてるか、MimetypeがALLOWED_MIME_TYPESに含まれてるならおｋ
        return in_array($majorType, self::ALLOWED_MIME_MAJOR_TYPES, true) || in_array($filetype, self::ALLOWED_MIME_TYPES, true);
    }

    private function rejectUpload(int $statusCode, string $message)
    {
        return response()->json([
            "HTTPResponse" => [
                "StatusCode" => $statusCode,
                "Body" => json_encode(["message" => $message]),
                "Header" => [
                    "Content-Type" => "application/json"
                ]
            ],
            "RejectUpload" => true
        ], 200);
    }

    private function generateUniquePath(string $fileName, string $mimeType): string
    {
        $majorType = explode("/", $mimeType)[0];
        $directory = "uploads/";

        if ($majorType === "image") {
            $directory .= "images/";
        }
        else if ($majorType === "audio") {
            $directory .= "audios/";
        }
        else if ($majorType === "video") {
            $directory .= "videos/";
        }
        else if ($mimeType === "application/zip") {
            $directory .= "mangas/";
        }

        $mimeTypes = new MimeTypes();
        $extension = $mimeTypes->getExtensions($mimeType)[0];

        $uniqueFileName = $this->fileService->getUniqueFileName(
            $directory,
            pathinfo($fileName, PATHINFO_FILENAME),
            $extension
        );

        return "./{$directory}{$uniqueFileName}";
    }

    private function generateUniqueFolderPath(string $fileName): string
    {
        $directory = "uploads/mangas/";
        $uniqueFolderName = $this->fileService->getUniqueFolderName(
            $directory,
            pathinfo($fileName, PATHINFO_FILENAME),
        );

        return "./{$directory}{$uniqueFolderName}";
    }
}
