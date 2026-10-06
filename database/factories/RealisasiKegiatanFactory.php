<?php

namespace Database\Factories;

use App\Models\KerjaSama;
use App\Models\RealisasiKegiatan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RealisasiKegiatan> */
class RealisasiKegiatanFactory extends Factory
{
    protected $model = RealisasiKegiatan::class;

    public function definition(): array
    {
        return [
            'kerja_sama_id' => KerjaSama::factory(),
            'judul_kegiatan' => fake()->sentence(3),
            'dharma' => 'pendidikan',
            'tanggal_mulai' => today()->subMonths(3),
            'tanggal_selesai' => today()->subMonth(),
            'manfaat_bagi_prodi' => fake()->sentence(),
            'status_verifikasi' => 'draf',
            'dibuat_oleh' => User::factory(),
        ];
    }

    public function status(string $status): static
    {
        return $this->state(['status_verifikasi' => $status]);
    }
}
