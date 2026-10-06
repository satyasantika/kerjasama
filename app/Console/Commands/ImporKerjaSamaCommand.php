<?php

namespace App\Console\Commands;

use App\Enums\Peran;
use App\Models\User;
use App\Services\ImporKerjaSama;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('kerjasama:impor {folder : Folder berisi prodi.csv, mitra.csv, kerja_sama.csv, realisasi_kegiatan.csv} {--user= : Surel pengguna pencatat (bawaan: super admin pertama)}')]
#[Description('Impor data awal kerja sama dari berkas CSV (idempoten)')]
class ImporKerjaSamaCommand extends Command
{
    public function handle(): int
    {
        $folder = str_starts_with($this->argument('folder'), '/')
            ? $this->argument('folder')
            : base_path($this->argument('folder'));

        $pembuat = $this->option('user')
            ? User::where('email', $this->option('user'))->first()
            : User::role(Peran::SuperAdmin->value)->orderBy('id')->first();

        if (! $pembuat) {
            $this->error('Pengguna pencatat tidak ditemukan. Jalankan seeder atau isi --user.');

            return self::FAILURE;
        }

        try {
            $hasil = (new ImporKerjaSama($folder, $pembuat))->jalankan();
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Data', 'Dibuat', 'Diperbarui', 'Dilewati'],
            collect($hasil['hitung'])->map(fn ($h, $k) => [$k, $h['dibuat'] ?? 0, $h['diperbarui'] ?? 0, $h['dilewati'] ?? 0])->values()->all(),
        );

        foreach ($hasil['peringatan'] as $p) {
            $this->warn($p);
        }

        foreach ($hasil['galat'] as $g) {
            $this->error($g);
        }

        return $hasil['galat'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
