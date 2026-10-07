<?php

namespace App\Actions\Pengguna;

use App\Enums\Peran;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AkhiriMenyamar
{
    /** Kembali ke akun Super Admin asal. Mengembalikan false bila tidak sedang menyamar. */
    public function handle(): bool
    {
        $asalId = session()->pull(MulaiMenyamar::KUNCI_SESI);
        $target = Auth::user();

        if ($asalId === null) {
            return false;
        }

        $asal = User::find($asalId);

        if ($asal === null || ! $asal->aktif || ! $asal->hasRole(Peran::SuperAdmin->value)) {
            Auth::guard()->logout();
            session()->invalidate();
            session()->regenerateToken();

            return true;
        }

        Log::notice('Penyamaran berakhir', ['penyamar_id' => $asal->getKey(), 'target_id' => $target?->getKey()]);

        Auth::guard()->login($asal);
        session()->forget('password_hash_'.Auth::getDefaultDriver());

        return true;
    }
}
