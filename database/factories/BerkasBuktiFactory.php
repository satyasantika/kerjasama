<?php

namespace Database\Factories;

use App\Models\BerkasBukti;
use App\Models\RealisasiKegiatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BerkasBukti> */
class BerkasBuktiFactory extends Factory
{
    protected $model = BerkasBukti::class;

    public function definition(): array
    {
        return [
            'realisasi_kegiatan_id' => RealisasiKegiatan::factory(),
            'nama_berkas' => 'laporan.pdf',
            'path' => 'bukti/laporan.pdf',
            'jenis' => 'laporan',
            'ukuran_kb' => 12,
        ];
    }
}
