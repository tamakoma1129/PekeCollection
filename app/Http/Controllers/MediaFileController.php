<?php

namespace App\Http\Controllers;

use App\Http\Requests\MediaFile\DestroyRequest;
use App\Http\Requests\MediaFile\IndexRequest;
use App\Http\Requests\MediaFile\TusdHookRequest;
use App\Http\Requests\MediaFile\UpdateReuqest;
use App\Models\MediaFile;
use App\Services\File\Exceptions\FileAlreadyExistsException;
use App\UseCases\MediaFile\DestroyAction;
use App\UseCases\MediaFile\IndexAction;
use App\UseCases\MediaFile\TusdHooks\PostFinishAction;
use App\UseCases\MediaFile\TusdHooks\PreCreateAction;
use App\UseCases\MediaFile\UpdateAction;
use Inertia\Inertia;

class MediaFileController extends Controller
{
    /**
     *  tusdからフックされて通信が送られる。１個のファイルアップロードにつき以下の通信、計4回が来る。
     *  詳細は「https://tus.github.io/tusd/advanced-topics/hooks/#list-of-available-hooks」
     * - pre-create
     * - post-create
     * - post-receive
     * - pre-finish(有効化時のみ)
     * - post-finish
     * - post-terminate(キャンセル時のみ)
     *
     * @param TusdHookRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function tusdHooks(
        TusdHookRequest $request,
        PreCreateAction $preCreateAction,
        PostFinishAction $postFinishAction
    )
    {
        $type = $request->input('Type');

        switch ($type) {
            case "pre-create":
                return $preCreateAction($request);
            case "post-create":
                return response()->json(["status" => 200]);
            case "post-receive":
                return response()->json(["status" => 200]);
            case "post-finish":
                return $postFinishAction($request);
            case "post-terminate":
                return response()->json(["status" => 200]);
        }

        return response()->json(["status" => 500]);
    }
    public function index(IndexRequest $request, IndexAction $action, string $mediaType)
    {
        $word = $request->input("word");
        $tags = $request->input("tags");
        $orientation = $request->input("orientation");

        $medias = $action($word, $tags, $orientation, $mediaType);
        return Inertia::render('Media/Index/Index', [
            'medias' => Inertia::merge($medias->items()),
            'currentPage' => $medias->currentPage(),
            'lastPage' => $medias->lastPage(),
            'mediaType' => $mediaType,
        ]);
    }

    public function update(UpdateReuqest $request, MediaFile $mediaFile, UpdateAction $action)
    {
        $validated = $request->validated();

        try {
            $action($mediaFile, $validated);
            return redirect()->back();
        } catch (FileAlreadyExistsException $e) {
            return redirect()->back()->withErrors([
                "message" => $e->getMessage()
            ]);
        }
    }

    public function destroy(DestroyRequest $request, DestroyAction $action)
    {
        $media_ids = $request->input("media_ids");

        $action($media_ids);
        return redirect()->back();
    }
}
