<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reverse proxy membuang prefix subpath (mis. /kerjasama) sebelum meneruskan
 * permintaan, sehingga Laravel mengira aplikasi berada di root. Middleware ini
 * memberi tahu request tentang base URL itu lewat X-Forwarded-Prefix, agar
 * URL relatif (mis. Livewire getUpdateUri()) tidak membawa prefix dua kali.
 */
class AturSubpath
{
    public function handle(Request $request, Closure $next): Response
    {
        $subPath = trim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/');

        if ($subPath !== '') {
            // Symfony menambahkan X-Forwarded-Prefix ke getBaseUrl() tanpa
            // memengaruhi path info, sehingga routing tetap memakai path tanpa prefix.
            $request->headers->set('X-Forwarded-Prefix', '/'.$subPath);
        }

        return $next($request);
    }
}
