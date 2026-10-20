<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('kerjasama:kirim-pengingat')->dailyAt('07:00')->withoutOverlapping();

// Detak penjadwal: dibaca /api/health (peringatan bila > 5 menit). Cap waktu (int), bukan objek Carbon:
// cache database tidak mengembalikan objek (cache.serializable_classes = false).
Schedule::call(fn () => cache()->put(config('app.kode').':detak-penjadwal', now()->getTimestamp(), 600))
    ->everyMinute()->name('detak-penjadwal');
