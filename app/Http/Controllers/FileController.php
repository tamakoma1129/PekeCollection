<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function show(string $path="")
    {
        if ($path==="") abort(404);
        if (!Storage::disk("private")->exists($path)) abort(404);

        $filePath = Storage::disk("private")->path($path);

        return response()->file($filePath,['Cache-Control' => 'public, max-age=3600']);
    }
}
