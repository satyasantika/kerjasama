<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kata sandi awal dibuat admin, bukan dikirim lewat surel. Karena itu pengguna dengan
 * flag wajib_ganti_sandi dipaksa membuka halaman profil sebelum memakai panel lain.
 */
class PaksaGantiSandi
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->wajib_ganti_sandi) {
            return $next($request);
        }

        $panel = Filament::getCurrentOrDefaultPanel();
        $profil = $panel->getProfileUrl();

        if ($request->routeIs('filament.*.auth.*') || $request->is('livewire*', 'livewire-*/*') || $request->url() === $profil) {
            return $next($request);
        }

        return redirect()->to($profil);
    }
}
