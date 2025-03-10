<?php

use App\Http\Controllers\MediaFileController;
use Illuminate\Support\Facades\Route;

Route::post('tusd-hooks', [MediaFileController::class, 'tusdHooks'])
    ->middleware(["web", "auth"])
    ->name('tusd-hooks');
