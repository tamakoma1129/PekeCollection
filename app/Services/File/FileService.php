<?php

namespace App\Services\File;

use App\Services\File\Exceptions\FileAlreadyExistsException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * @param UploadedFile $uploadedFile
     * @param string $directory
     * @param string $filename
     * @return false|string
     */
    public function saveFile(UploadedFile $uploadedFile, string $directory, string $filename, string $diskName="private")
    {
        $directory = rtrim($directory, '/');

        if (Storage::disk($diskName)->exists($directory.$filename)) {
            throw new FileAlreadyExistsException('同ファイル名のファイルが既に存在します');
        }

        $path = $uploadedFile->storeAs($directory, $filename,"private");
        if(!$path) {
            throw new \Exception("ファイルの保存に失敗しました");
        }

        return $path;
    }

    public function makeDirectory(string $directoryRelPath, string $diskName="private"): void
    {
        $directoryRelPath = rtrim($directoryRelPath, '/');
        if (Storage::disk($diskName)->directoryExists($directoryRelPath)) {
            throw new \Exception("ディレクトリが既に存在しています");
        }

        Storage::disk($diskName)->makeDirectory($directoryRelPath);
    }

    /**
     * 禁止半角文字を全角に
     * @param  string $fileName
     * @return string
     */
    private function convertToValidWindowsFileName(string $filename)
    {
        $from = ['\\', '/', ':', '*', '?', '"', '>', '<', '|'];
        $to = ['￥', '／', '：', '＊', '？', '”', '＞', '＜', '｜'];
        return str_replace($from, $to, $filename);
    }

    /**
     *  名前が被っていてもディレクトリで一意なファイル名を生成する。例:hogehoge(1)
     *  $directoryは最後に/が無くても可。$extensionは最初に.が無くても可。
     *
     * @param string $directory
     * @param string $fileName
     * @param string $extension
     * @param string|null $disk
     * @return string
     */
    public function getUniqueFileName(string $directoryRelPath, string $fileName, string $extension, ?string $disk = "private")
    {
        $fileName = $this->convertToValidWindowsFileName($fileName);
        $extension = ltrim($extension, '.');
        $directoryRelPath = rtrim($directoryRelPath, '/').'/';

        // 初期ファイル名を生成
        $uniqueFileName = "{$fileName}.{$extension}";
        $counter = 1;

        // 重複がある限り、ファイル名に数字を追加
        while (Storage::disk($disk)->fileExists("{$directoryRelPath}{$uniqueFileName}")) {
            $uniqueFileName = "{$fileName}({$counter}).{$extension}";
            $counter++;
        }

        return $uniqueFileName;
    }

    public function getUniqueFolderName(string $directoryRelPath, string $folderName, ?string $disk = "private")
    {
        $folderName = $this->convertToValidWindowsFileName($folderName);
        $directoryRelPath = rtrim($directoryRelPath, '/').'/';

        // 初期フォルダ名を生成
        $uniqueFolderName = $folderName;
        $counter = 1;

        // 重複がある限り、ファイル名に数字を追加
        while (Storage::disk($disk)->directoryExists("{$directoryRelPath}{$uniqueFolderName}")) {
            $uniqueFolderName = "{$folderName}({$counter})";
            $counter++;
        }

        return $uniqueFolderName;
    }

    public function copyFile(string $oldFilePath, string $newFilePath, ?string $disk = "private") {
        if (Storage::disk($disk)->exists($newFilePath)) {
            throw new FileAlreadyExistsException('同ファイル名のファイルが既に存在します');
        }
        Storage::disk($disk)->copy($oldFilePath, $newFilePath);
    }

    public function moveFile(string $oldFilePath, string $newFilePath, ?string $disk = "private") {
        if (Storage::disk($disk)->exists($newFilePath)) {
            throw new FileAlreadyExistsException('同ファイル名のファイルが既に存在します');
        }
        Storage::disk($disk)->move($oldFilePath, $newFilePath);
    }

    public function copyFolder(string $oldFolderPath, string $newFolderPath, ?string $disk = "private") {
        if (Storage::disk($disk)->directoryExists($newFolderPath)) {
            throw new FileAlreadyExistsException('同フォルダ名のフォルダが既に存在します');
        }
        $oldFullPath = Storage::disk($disk)->path($oldFolderPath);
        $newFullPath = Storage::disk($disk)->path($newFolderPath);
        File::copyDirectory($oldFullPath, $newFullPath);
    }

    public function moveFolder(string $oldFolderPath, string $newFolderPath, ?string $disk = "private") {
        if (Storage::disk($disk)->directoryExists($newFolderPath)) {
            throw new FileAlreadyExistsException('同フォルダ名のフォルダが既に存在します');
        }
        $oldFullPath = Storage::disk($disk)->path($oldFolderPath);
        $newFullPath = Storage::disk($disk)->path($newFolderPath);
        File::moveDirectory($oldFullPath, $newFullPath);
    }

    public function deleteFile(string $filePath, ?string $disk = "private") {
        if (Storage::disk($disk)->exists($filePath)) {
            Storage::disk($disk)->delete($filePath);
        } else {
            logger()->warning("削除しようとしたファイルが存在しませんでした。");
        }
    }

    public function deleteFolder(string $folderPath, ?string $disk = "private") {
        if (Storage::disk($disk)->directoryExists($folderPath)) {
            $fullPath = Storage::disk($disk)->path($folderPath);
            File::deleteDirectory($fullPath);
        } else {
            logger()->warning("削除しようとしたフォルダが存在しませんでした。");
        }
    }
}
