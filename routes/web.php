<?php

use App\Http\Controllers\AkhiriMenyamarController;
use App\Http\Controllers\BerkasBuktiController;
use App\Http\Controllers\BerkasKerjaSamaController;
use App\Http\Controllers\LaporanPimpinanController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('beranda');

Route::middleware('auth')->group(function () {
    Route::post('/menyamar/akhiri', AkhiriMenyamarController::class)->name('menyamar.akhiri');

    Route::get('/kerja-sama/{kerjaSama}/berkas/{jenis}', BerkasKerjaSamaController::class)
        ->whereIn('jenis', ['dokumen', 'asing'])
        ->name('kerja-sama.berkas');

    Route::get('/laporan/rekap-pimpinan', LaporanPimpinanController::class)->name('laporan.rekap-pimpinan');

    Route::get('/bukti/{berkasBukti}', BerkasBuktiController::class)->name('bukti.unduh');
});
