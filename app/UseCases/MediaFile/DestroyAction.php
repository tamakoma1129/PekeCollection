<?php

namespace App\UseCases\MediaFile;

use App\Models\MediaFile;
use App\Models\Tag;
use App\Services\File\FileService;
use Illuminate\Support\Facades\DB;

class DestroyAction
{
    protected FileService $fileService;
    public function __construct(FileService $fileService){
        $this->fileService = $fileService;
    }

    public function __invoke(array $media_ids)
    {
        foreach ($media_ids as $media_id) {
            $this->destroy($media_id);
        }
        Tag::doesntHave('mediaFiles')->delete();
    }

    /**
     * 途中でエラーが発生してもできるだけDBとファイルに齟齬ができないように1個ずつ削除する
     *
     * @param int $media_id
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Throwable
     */
    public function destroy (int $media_id)
    {
        DB::transaction(function () use ($media_id) {
            $mediaFile = MediaFile::findOrFail($media_id);
            $path = $mediaFile->path;
            $extraPath = $mediaFile->createExtraPath($mediaFile->mediable_type, pathinfo($path, PATHINFO_BASENAME));

            $this->fileService->deleteFolder($extraPath);
            if ($mediaFile->mediable_type === "App\Models\Manga") {
                $this->fileService->deleteFolder($path);
            } else {
                $this->fileService->deleteFile($path);
            }

            if ($mediaFile->mediable) {
                $mediaFile->mediable->delete();
            }
            $mediaFile->delete();
        });
    }
}
