<?php

namespace App\Http\Controllers;


use App\Http\Requests\Tag\AttachRequest;
use App\Http\Requests\Tag\DetachRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\UseCases\Tag\AttachAction;
use App\UseCases\Tag\DetachAction;


class TagController extends Controller
{
    public function get()
    {
        $tags = Tag::withCount('mediaFiles')
            ->orderBy('media_files_count', 'desc')
            ->get();

        return TagResource::collection($tags);
    }

    public function attach(AttachRequest $request, AttachAction $action)
    {
        $mediaIds = $request->input('media_ids');
        $tags = $request->input('tags');

        $action($mediaIds, $tags);
        return redirect()->back();
    }

    public function detach(DetachRequest $request, DetachAction $action)
    {
        $mediaIds = $request->input('media_ids');
        $tags = $request->input('tags');

        $action($mediaIds, $tags);
        return redirect()->back();
    }
}
