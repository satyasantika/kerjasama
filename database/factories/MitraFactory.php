<?php

namespace Database\Factories;

use App\Models\Mitra;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Mitra> */
class MitraFactory extends Factory
{
    protected $model = Mitra::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->company(),
            'jenis' => 'sekolah',
            'negara' => 'Indonesia',
        ];
    }

    public function asing(string $negara = 'Malaysia'): static
    {
        return $this->state(['jenis' => 'perguruan_tinggi', 'negara' => $negara, 'status_legal' => 'Terakreditasi']);
    }
}
