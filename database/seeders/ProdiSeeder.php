<?php

namespace Database\Seeders;

use App\Models\Prodi;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    /**
     * Daftar prodi FKIP. Kode dan jenjang adalah usulan awal (belum resmi);
     * kode dipakai sebagai kunci impor CSV, jadi jangan diubah sembarangan.
     */
    private const DATA = [
        ['PMAS', 'Pendidikan Masyarakat', 'S1'],
        ['PBIN', 'Pendidikan Bahasa Indonesia', 'S1'],
        ['PBIG', 'Pendidikan Bahasa Inggris', 'S1'],
        ['PMAT', 'Pendidikan Matematika', 'S1'],
        ['PBIO', 'Pendidikan Biologi', 'S1'],
        ['PEKO', 'Pendidikan Ekonomi', 'S1'],
        ['PGEO', 'Pendidikan Geografi', 'S1'],
        ['PSEJ', 'Pendidikan Sejarah', 'S1'],
        ['PJAS', 'Pendidikan Jasmani', 'S1'],
        ['PFIS', 'Pendidikan Fisika', 'S1'],
        ['PKOR', 'Pendidikan Kepelatihan Olahraga', 'S1'],
        ['PSNI', 'Pendidikan Seni Pertunjukan', 'S1'],
        ['PPG', 'Pendidikan Profesi Guru', 'PPG'],
    ];

    public function run(): void
    {
        foreach (self::DATA as [$kode, $nama, $jenjang]) {
            // Hanya membuat yang belum ada; perubahan manual lewat UI tidak ditimpa.
            Prodi::firstOrCreate(['kode' => $kode], ['nama' => $nama, 'jenjang' => $jenjang, 'aktif' => true]);
        }
    }
}
