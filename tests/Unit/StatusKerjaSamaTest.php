<?php

use App\Enums\StatusKerjaSama;
use App\Enums\StatusManual;
use Carbon\Carbon;

// README §7 — status dihitung, urutan prioritas, ambang 90 hari.
$hari = Carbon::parse('2026-06-15');

it('belum_berlaku bila hari ini sebelum tanggal mulai', function () use ($hari) {
    expect(StatusKerjaSama::hitung(null, Carbon::parse('2026-06-16'), Carbon::parse('2029-06-15'), $hari))
        ->toBe(StatusKerjaSama::BelumBerlaku);
});

it('kedaluwarsa bila hari ini lewat tanggal berakhir', function () use ($hari) {
    expect(StatusKerjaSama::hitung(null, Carbon::parse('2023-01-01'), Carbon::parse('2026-06-14'), $hari))
        ->toBe(StatusKerjaSama::Kedaluwarsa);
});

it('masih aktif/akan berakhir pada hari terakhir berlaku', function () use ($hari) {
    expect(StatusKerjaSama::hitung(null, Carbon::parse('2023-01-01'), Carbon::parse('2026-06-15'), $hari))
        ->toBe(StatusKerjaSama::AkanBerakhir);
});

it('akan_berakhir bila sisa ≤ 90 hari dan aktif bila 91 hari', function () use ($hari) {
    $mulai = Carbon::parse('2025-01-01');

    expect(StatusKerjaSama::hitung(null, $mulai, $hari->copy()->addDays(90), $hari))->toBe(StatusKerjaSama::AkanBerakhir)
        ->and(StatusKerjaSama::hitung(null, $mulai, $hari->copy()->addDays(91), $hari))->toBe(StatusKerjaSama::Aktif);
});

it('aktif untuk kerja sama berlaku panjang', function () use ($hari) {
    expect(StatusKerjaSama::hitung(null, Carbon::parse('2026-01-01'), Carbon::parse('2030-01-01'), $hari))
        ->toBe(StatusKerjaSama::Aktif);
});

it('status manual menang atas semua aturan lain', function () use ($hari) {
    $mulai = Carbon::parse('2023-01-01');
    $berakhir = Carbon::parse('2023-12-31'); // sebenarnya kedaluwarsa

    expect(StatusKerjaSama::hitung(StatusManual::Dibatalkan, $mulai, $berakhir, $hari))->toBe(StatusKerjaSama::Dibatalkan)
        ->and(StatusKerjaSama::hitung(StatusManual::Dihentikan, $mulai, $berakhir, $hari))->toBe(StatusKerjaSama::Dihentikan);
});
