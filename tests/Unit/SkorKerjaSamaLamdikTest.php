<?php

use App\Services\SkorKerjaSamaLamdik;

// docs/rujukan.md §B.4 — kolom: N1 N2 N3 NDTPS NI NN NW Skor(b) → RK A B Skor(a) Skor
dataset('kasus_uji_lamdik', [
    'umum' => [[4, 2, 3, 6, 1, 3, 10, 3], [3.1667, 3.1667, 3.2500, 3.1944, 3.1458]],
    'batas atas' => [[10, 8, 6, 6, 2, 0, 0, 4], [8.6667, 4.0000, 4.0000, 4.0000, 4.0000]],
    'lokal cukup' => [[1, 1, 1, 10, 0, 0, 9, 2], [0.6000, 0.6000, 2.0000, 1.0667, 1.3000]],
    'lokal kurang' => [[1, 1, 1, 10, 0, 0, 8, 2], [0.6000, 0.6000, 1.0000, 0.7333, 1.0500]],
    'NN >= b' => [[2, 0, 0, 5, 1, 6, 0, 3], [1.2000, 1.2000, 3.5000, 1.9667, 2.2250]],
    'celah NI=0, NN<b' => [[2, 1, 0, 5, 0, 3, 4, 2], [1.6000, 1.6000, 2.5000, 1.9000, 1.9250]],
    'celah NI<a, NN=0' => [[2, 1, 0, 5, 1, 0, 4, 2], [1.6000, 1.6000, 3.0000, 2.0667, 2.0500]],
]);

it('lolos kasus uji B.4', function (array $masukan, array $harapan) {
    $hasil = (new SkorKerjaSamaLamdik)->hitung(...$masukan);

    foreach (['rk', 'a', 'b', 'skor_a', 'skor'] as $i => $kunci) {
        expect($hasil[$kunci])->toEqualWithDelta($harapan[$i], 0.00005);
        expect(round($hasil[$kunci], 4))->toBe(round($harapan[$i], 4));
    }
})->with('kasus_uji_lamdik');

it('menolak NDTPS nol tanpa membagi nol', function () {
    expect(fn () => (new SkorKerjaSamaLamdik)->hitung(1, 1, 1, 0, 0, 0, 0, 2))
        ->toThrow(DomainException::class, 'NDTPS bernilai 0');
});

it('menolak jumlah negatif dan skor (b) di luar 1–4', function () {
    $k = new SkorKerjaSamaLamdik;

    expect(fn () => $k->hitung(-1, 0, 0, 5, 0, 0, 0, 2))->toThrow(DomainException::class)
        ->and(fn () => $k->hitung(1, 0, 0, 5, 0, 0, 0, 0.5))->toThrow(DomainException::class)
        ->and(fn () => $k->hitung(1, 0, 0, 5, 0, 0, 0, 4.5))->toThrow(DomainException::class);
});

it('menghitung faktor B di setiap cabang', function () {
    $k = new SkorKerjaSamaLamdik;

    expect($k->hitungB(2, 0, 0))->toBe(4.0)
        ->and($k->hitungB(5, 9, 9))->toBe(4.0)
        ->and($k->hitungB(1, 6, 0))->toBe(3.5)
        ->and($k->hitungB(0, 6, 0))->toBe(3.0)
        ->and($k->hitungB(0, 0, 9))->toBe(2.0)
        ->and($k->hitungB(0, 0, 8))->toBe(1.0)
        ->and($k->hitungB(1, 3, 0))->toEqualWithDelta(3.25, 0.00001);
});

it('membatasi A pada 4 dan skor akhir tidak melebihi 4', function () {
    $hasil = (new SkorKerjaSamaLamdik)->hitung(100, 100, 100, 1, 10, 10, 10, 4);

    expect($hasil['a'])->toBe(4.0)->and($hasil['skor'])->toBe(4.0);
});
