<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\MediaFileController;
use App\Http\Controllers\TagController;
use App\Http\Middleware\CheckUserExists;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect(route("media.index", ["mediaType"=>"all"]));
});

Route::middleware(['auth', CheckUserExists::class])->group(function () {
    Route::get('/dashboard', function () {return Inertia::render('Dashboard');})->name('dashboard');

    Route::post("/tag", [TagController::class, 'attach'])->name('tag.attach');

    Route::get('/{mediaType}', [MediaFileController::class, 'index'])
        ->where('mediaType', 'all|image|audio|video|manga')
        ->name('media.index');

    Route::get('/private/{path?}', [FileController::class, 'show'])->where('path', '.*');

    Route::get('/tag', [TagController::class, 'get'])->name('tag.get');

    Route::get('/manga/create', function () {
        return Inertia::render('Manga/MangaUpload');
    })->name('manga.create');

    Route::patch('/media-file/{mediaFile}', [MediaFileController::class, 'update'])
        ->name('media_file.update');

    Route::delete('/tag', [TagController::class, 'detach'])->name('tag.detach');
    Route::delete('/media-file', [MediaFileController::class, 'destroy'])
        ->name('media_file.destroy');
});

require __DIR__.'/auth.php';
