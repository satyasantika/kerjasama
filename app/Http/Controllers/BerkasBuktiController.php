<?php

namespace App\Http\Controllers;

use App\Models\BerkasBukti;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BerkasBuktiController extends Controller
{
    /** Unduh bukti realisasi dari disk privat; hanya untuk yang berhak melihat (Policy). */
    public function __invoke(BerkasBukti $berkasBukti): StreamedResponse
    {
        Gate::authorize('view', $berkasBukti);

        abort_unless(Storage::disk('local')->exists($berkasBukti->path), 404);

        return Storage::disk('local')->download($berkasBukti->path, $berkasBukti->nama_berkas);
    }
}
