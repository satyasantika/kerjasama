<?php

namespace App\Http\Controllers;

use App\Models\KerjaSama;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BerkasKerjaSamaController extends Controller
{
    /** Unduh dokumen perjanjian dari disk privat; hanya untuk yang berhak melihat (Policy). */
    public function __invoke(KerjaSama $kerjaSama, string $jenis): StreamedResponse
    {
        Gate::authorize('view', $kerjaSama);

        $path = match ($jenis) {
            'dokumen' => $kerjaSama->berkas_dokumen,
            'asing' => $kerjaSama->berkas_dokumen_asing,
            default => abort(404),
        };

        abort_if(blank($path) || ! Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, str($kerjaSama->judul)->slug().'-'.$jenis.'.pdf');
    }
}
