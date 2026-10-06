<?php

use App\Http\Controllers\BerkasBuktiController;
use App\Http\Controllers\BerkasKerjaSamaController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::middleware('auth')->group(function () {
    Route::get('/kerja-sama/{kerjaSama}/berkas/{jenis}', BerkasKerjaSamaController::class)
        ->whereIn('jenis', ['dokumen', 'asing'])
        ->name('kerja-sama.berkas');

    Route::get('/bukti/{berkasBukti}', BerkasBuktiController::class)->name('bukti.unduh');
});
