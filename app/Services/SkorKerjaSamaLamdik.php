<?php

namespace App\Services;

use DomainException;

/**
 * Skor elemen Kerja Sama Tridharma LAMDIK IAPSK 3.0 (docs/rujukan.md §B).
 * Kelas murni: tanpa akses database, input angka → output angka.
 */
class SkorKerjaSamaLamdik
{
    /** Bobot jenis kerja sama untuk RK. */
    public const BOBOT_PENDIDIKAN = 3;
    public const BOBOT_PENELITIAN = 2;
    public const BOBOT_PKM = 1;

    /** Faktor tingkat: a (internasional), b (nasional), c (wilayah/lokal). */
    public const FAKTOR_INTERNASIONAL = 2;
    public const FAKTOR_NASIONAL = 6;
    public const FAKTOR_LOKAL = 9;

    /**
     * @return array{rk: float, a: float, b: float, skor_a: float, skor: float}
     *
     * @throws DomainException bila NDTPS = 0 (tidak dapat dihitung) atau input tidak sah
     */
    public function hitung(
        int $n1,
        int $n2,
        int $n3,
        int $ndtps,
        int $ni,
        int $nn,
        int $nw,
        float $skorB,
    ): array {
        if ($ndtps <= 0) {
            throw new DomainException('NDTPS bernilai 0 sehingga skor tidak dapat dihitung. Isi NDTPS prodi terlebih dahulu.');
        }

        if (min($n1, $n2, $n3, $ni, $nn, $nw) < 0) {
            throw new DomainException('Jumlah kerja sama tidak boleh negatif.');
        }

        if ($skorB < 1 || $skorB > 4) {
            throw new DomainException('Skor (b) harus antara 1 dan 4.');
        }

        $rk = (self::BOBOT_PENDIDIKAN * $n1 + self::BOBOT_PENELITIAN * $n2 + self::BOBOT_PKM * $n3) / $ndtps;
        $a = min($rk, 4.0);
        $b = $this->hitungB($ni, $nn, $nw);
        $skorA = (2 * $a + $b) / 3;
        $skor = (3 * $skorA + $skorB) / 4;

        return ['rk' => $rk, 'a' => $a, 'b' => $b, 'skor_a' => $skorA, 'skor' => $skor];
    }

    /**
     * Faktor B (tingkat kerja sama). Urutan pengecekan (⚠ ASUMSI untuk celah matriks, rujukan §B.1):
     * NI ≥ a → NN ≥ b → NI = 0 dan NN = 0 → rumus umum.
     */
    public function hitungB(int $ni, int $nn, int $nw): float
    {
        $a = self::FAKTOR_INTERNASIONAL;
        $b = self::FAKTOR_NASIONAL;
        $c = self::FAKTOR_LOKAL;

        return match (true) {
            $ni >= $a => 4.0,
            $nn >= $b => 3 + $ni / $a,
            $ni === 0 && $nn === 0 => $nw >= $c ? 2.0 : 1.0,
            default => 2 + 2 * ($ni / $a) + $nn / $b - ($ni * $nn) / ($a * $b),
        };
    }
}
