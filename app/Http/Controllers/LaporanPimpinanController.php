<?php

namespace App\Http\Controllers;

use App\Models\KerjaSama;
use App\Services\RekapPimpinan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class LaporanPimpinanController extends Controller
{
    public function __invoke(RekapPimpinan $rekap): Response
    {
        Gate::authorize('viewAny', KerjaSama::class);

        return Pdf::loadView('laporan.rekap-pimpinan', $rekap->data())
            ->setPaper('a4', 'portrait')
            ->download('rekap-kerja-sama-'.now()->format('Y-m-d').'.pdf');
    }
}
