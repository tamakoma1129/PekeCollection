<?php

namespace App\Jobs;

use App\Models\Manga;
use App\Services\Image\ImageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ProcessMangaUpload implements ShouldQueue
{
    use Queueable;

    protected $mangaId;

    public function __construct(int $mangaId)
    {
        $this->mangaId = $mangaId;
    }

    /**
     * jobの再試行はユーザー側の操作が必要 & 再試行したところで治る可能性が低いため、失敗時はMangaファイルのアップロードを無かったことにする。
     * ユーザーにはそれを通知するシステムにできたらいいが、まだ作ってない。
     */
    public function handle(ImageService $imageService): void
    {
        $manga = Manga::where('id', $this->mangaId)->with(['pages', 'mediaFile'])->firstOrFail();
        $mangaPages = $manga->pages;
        $directoryOriginal = "uploads/mangas/{$manga->mediaFile->base_name}/";
        $directoryExtra = "extras/mangas/{$manga->mediaFile->base_name}/";

        try {
            foreach ($mangaPages as $page) {
                $imageService->generateLiteImage($page->path ,$page->lite_path);
            }
        } catch (\Throwable $e) {
            // 保存済みのMangaがある場合は削除
            if ($directoryOriginal && Storage::disk('private')->directoryExists($directoryOriginal)) {
                logger()->error("エラー発生により保存したディレクトリを削除： {$directoryOriginal}");
                Storage::disk('private')->deleteDirectory($directoryOriginal);
            }

            // 保存済みのExtrasフォルダがあれば削除
            if ($directoryExtra && Storage::disk('private')->directoryExists($directoryExtra)) {
                logger()->error("エラー発生により保存したディレクトリを削除： {$directoryExtra}");
                Storage::disk('private')->deleteDirectory($directoryExtra);
            }

            // DBからMangaデータを削除(pagesはcascadeで削除される)
            $manga->mediaFile->delete();
            $manga->delete();

            throw $e;
        }
    }
}
