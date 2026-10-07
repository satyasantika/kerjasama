<?php

namespace App\Http\Controllers;

use App\Models\BerkasBukti;
use App\Models\KerjaSama;
use App\Rules\TautanBerkasValid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/** Membuka tautan Google Drive hanya untuk yang berhak (Policy) dan hanya ke host daftar putih. */
class TautanBerkasController extends Controller
{
    public function kerjaSama(KerjaSama $kerjaSama, string $jenis): RedirectResponse
    {
        Gate::authorize('view', $kerjaSama);

        $url = match ($jenis) {
            'dokumen' => $kerjaSama->tautan_dokumen,
            'asing' => $kerjaSama->tautan_dokumen_asing,
            default => abort(404),
        };

        return $this->buka($url);
    }

    public function bukti(BerkasBukti $berkasBukti): RedirectResponse
    {
        Gate::authorize('view', $berkasBukti);

        return $this->buka($berkasBukti->tautan);
    }

    private function buka(?string $url): RedirectResponse
    {
        abort_unless(TautanBerkasValid::sah($url), 404);

        return redirect()->away($url);
    }
}
