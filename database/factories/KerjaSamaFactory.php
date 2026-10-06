<?php

namespace Database\Factories;

use App\Models\KerjaSama;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<KerjaSama> */
class KerjaSamaFactory extends Factory
{
    protected $model = KerjaSama::class;

    public function definition(): array
    {
        return [
            'jenis_dokumen' => 'MoA',
            'judul' => fake()->sentence(4),
            'ruang_lingkup' => fake()->sentence(),
            'bidang' => 'akademik',
            'tingkat' => 'lokal',
            'pihak_penandatangan_unsil' => 'dekan',
            'tanggal_tanda_tangan' => today()->subYear(),
            'tanggal_mulai' => today()->subYear(),
            'tanggal_berakhir' => today()->addYears(2),
            'dibuat_oleh' => User::factory(),
        ];
    }

    public function berlaku(string $mulai, string $berakhir): static
    {
        return $this->state(['tanggal_mulai' => $mulai, 'tanggal_berakhir' => $berakhir]);
    }
}
