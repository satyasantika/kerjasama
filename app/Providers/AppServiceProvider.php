<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // asset(), dan skrip Livewire (yang juga memakai helper url())
        // menghasilkan /kerjasama/... dan bukan /...
        $appUrl = rtrim((string) config('app.url'), '/');
        $subPath = trim((string) parse_url($appUrl, PHP_URL_PATH), '/');

        if ($subPath !== '') {
            URL::forceRootUrl($appUrl);
        }

        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('google-login', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));

        FilamentView::registerRenderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
            fn () => view('auth.tombol-google'),
        );
    }
}
