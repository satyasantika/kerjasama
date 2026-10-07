<?php

namespace App\Actions\Pengguna;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class MulaiMenyamar
{
    /** Kunci sesi berisi id Super Admin asal selama penyamaran berlangsung. */
    public const KUNCI_SESI = 'penyamar_id';

    public function handle(User $pelaku, User $target): void
    {
        Gate::forUser($pelaku)->authorize('impersonate', $target);

        Log::notice('Penyamaran dimulai', ['penyamar_id' => $pelaku->getKey(), 'target_id' => $target->getKey()]);

        Auth::guard()->login($target);

        // Hash sandi di sesi milik penyamar; hapus agar AuthenticateSession menyimpan milik target.
        session()->forget('password_hash_'.Auth::getDefaultDriver());
        session()->put(self::KUNCI_SESI, $pelaku->getKey());
    }
}
