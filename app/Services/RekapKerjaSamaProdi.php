<?php

namespace App\Services;

use App\Enums\Dharma;
use App\Enums\TingkatKerjaSama;
use App\Models\EvaluasiKerjaSama;
use App\Models\Prodi;

/**
 * Menyiapkan angka masukan skor LAMDIK dari data sistem. Mengikuti ASUMSI README §9:
 * satu baris = satu realisasi kegiatan terverifikasi prodi pada rentang TS-2 s.d. TS,
 * dikelompokkan menurut dharma; tingkat diambil dari dokumen kerja samanya.
 */
class RekapKerjaSamaProdi
{
    public function __construct(private readonly EksporKerjaSamaTridharma $ekspor = new EksporKerjaSamaTridharma) {}

    /** @return array{n1: int, n2: int, n3: int, ni: int, nn: int, nw: int} */
    public function jumlah(Prodi $prodi, int $tahunTs): array
    {
        $hasil = ['n1' => 0, 'n2' => 0, 'n3' => 0, 'ni' => 0, 'nn' => 0, 'nw' => 0];
        $kunciDharma = [Dharma::Pendidikan->value => 'n1', Dharma::Penelitian->value => 'n2', Dharma::Pkm->value => 'n3'];
        $kunciTingkat = [
            TingkatKerjaSama::Internasional->value => 'ni',
            TingkatKerjaSama::Nasional->value => 'nn',
            TingkatKerjaSama::Lokal->value => 'nw',
        ];

        foreach (Dharma::cases() as $dharma) {
            foreach ($this->ekspor->kegiatan($prodi, $tahunTs, $dharma) as $kegiatan) {
                $hasil[$kunciDharma[$dharma->value]]++;
                $hasil[$kunciTingkat[$kegiatan->kerjaSama->tingkat->value]]++;
            }
        }

        return $hasil;
    }

    public function ndtps(Prodi $prodi, int $tahunTs): ?int
    {
        return $prodi->ndtps()->where('tahun_ts', $tahunTs)->value('ndtps');
    }

    /**
     * ⚠ ASUMSI: skor (b) prodi = rata-rata skor evaluasi kerja sama yang melibatkan prodi
     * pada rentang TS-2 s.d. TS, dibulatkan 2 desimal. Dapat ditimpa manual di kalkulator.
     */
    public function skorB(Prodi $prodi, int $tahunTs): ?float
    {
        $rata = EvaluasiKerjaSama::query()
            ->whereBetween('tahun_ts', [$tahunTs - 2, $tahunTs])
            ->whereHas('kerjaSama.prodi', fn ($q) => $q->where('prodi.id', $prodi->id))
            ->avg('skor_keefektifan');

        return $rata === null ? null : round((float) $rata, 2);
    }
}
