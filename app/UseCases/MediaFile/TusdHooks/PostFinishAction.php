<?php

namespace App\UseCases\MediaFile\TusdHooks;

use App\Http\Requests\MediaFile\TusdHookRequest;
use App\Jobs\TusdHooks\PostFinish\ProcessAudio;
use App\Jobs\TusdHooks\PostFinish\ProcessImage;
use App\Jobs\TusdHooks\PostFinish\ProcessManga;
use App\Jobs\TusdHooks\PostFinish\ProcessVideo;

class PostFinishAction
{
    public function __invoke(TusdHookRequest $request)
    {
        $uploadData = [
            "fileName" => $request->input("Event.Upload.MetaData.filename"),
            "mimeType" => $request->input("Event.Upload.MetaData.mimetype"),
            "fileSize" => $request->input("Event.Upload.Size"),
            "path" => $request->input("Event.Upload.Storage.Path"),
            "infoPath" => $request->input("Event.Upload.Storage.InfoPath"),
        ];

        $mimeType = $uploadData["mimeType"];
        $majorType = explode("/", $mimeType)[0];

        if ($majorType === "image") {
            ProcessImage::dispatch($uploadData);
        }
        else if ($majorType === "audio") {
            ProcessAudio::dispatch($uploadData);
        }
        else if ($majorType === "video") {
            ProcessVideo::dispatch($uploadData);
        }
        else if ($mimeType === "application/zip") {
            ProcessManga::dispatch($uploadData);
        }

        return response()->json(["status" => 200]);
    }
}
