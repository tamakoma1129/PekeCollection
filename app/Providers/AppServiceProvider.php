<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // APIをdataで包まない
        JsonResource::withoutWrapping();

        // Pluseへのアクセス権限。ログインしてたらおｋ
        Gate::define('viewPulse', function () {
            return auth()->check();
        });
    }
}
