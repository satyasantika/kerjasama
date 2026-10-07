<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        // Aplikasi dapat dipasang di subpath di belakang reverse proxy
        // (mis. https://supportfkip.unsil.ac.id/kerjasama). Permintaan yang
        // sampai ke container tidak membawa prefix path itu, jadi root URL
        // Laravel harus dipaksa mengikuti APP_URL agar route(), url(),
        // asset(), dan redirect menghasilkan /kerjasama/... bukan /...
        $appUrl = rtrim((string) config('app.url'), '/');
        $subPath = trim((string) parse_url($appUrl, PHP_URL_PATH), '/');

        if ($subPath !== '') {
            URL::forceRootUrl($appUrl);
        }

        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }

        // Skrip Livewire dibangun dari rute mentahnya sendiri (tidak lewat
        // forceRootUrl di atas), jadi prefix subpath harus ditempelkan
        // langsung ke rute itu di sini, jika tidak <script src> kehilangan
        // /kerjasama sama sekali.
        if ($subPath !== '') {
            Livewire::setScriptRoute(function ($handle) use ($subPath) {
                return Route::get($subPath.'/livewire/livewire.min.js', $handle);
            });
        }
    }
}
