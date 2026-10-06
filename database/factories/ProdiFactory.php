<?php

namespace Database\Factories;

use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Prodi> */
class ProdiFactory extends Factory
{
    protected $model = Prodi::class;

    public function definition(): array
    {
        return [
            'kode' => strtoupper(fake()->unique()->bothify('P###??')),
            'nama' => 'Pendidikan '.fake()->unique()->word(),
            'jenjang' => 'S1',
            'aktif' => true,
        ];
    }
}
