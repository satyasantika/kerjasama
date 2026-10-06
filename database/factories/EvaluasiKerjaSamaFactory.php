<?php

namespace Database\Factories;

use App\Models\EvaluasiKerjaSama;
use App\Models\KerjaSama;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EvaluasiKerjaSama> */
class EvaluasiKerjaSamaFactory extends Factory
{
    protected $model = EvaluasiKerjaSama::class;

    public function definition(): array
    {
        return [
            'kerja_sama_id' => KerjaSama::factory(),
            'tahun_ts' => now()->year,
            'skor_keefektifan' => 3,
            'analisis' => fake()->sentence(),
            'rekomendasi' => 'lanjutkan',
            'dinilai_oleh' => User::factory(),
        ];
    }
}
