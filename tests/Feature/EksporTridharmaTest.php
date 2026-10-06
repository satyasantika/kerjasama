<?php

use App\Enums\Dharma;
use App\Enums\Peran;
use App\Filament\Resources\RealisasiKegiatan\Pages\ListRealisasiKegiatan;
use App\Models\KerjaSama;
use App\Models\Mitra;
use App\Models\Prodi;
use App\Models\RealisasiKegiatan;
use App\Models\User;
use App\Services\EksporKerjaSamaTridharma;
use Database\Seeders\RolSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;
use OpenSpout\Reader\XLSX\Reader;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(RolSeeder::class);
    Filament::setCurrentPanel('admin');
    $this->prodi = Prodi::factory()->create(['kode' => 'PMAT']);
});

function kegiatan(Prodi $prodi, string $dharma, string $mulai, string $status = 'terverifikasi', ?KerjaSama $ks = null, ?string $selesai = null): RealisasiKegiatan
{
    $r = RealisasiKegiatan::factory()->status($status)->create([
        'kerja_sama_id' => ($ks ?? KerjaSama::factory()->create())->id,
        'dharma' => $dharma,
        'tanggal_mulai' => $mulai,
        'tanggal_selesai' => $selesai,
        'judul_kegiatan' => "Kegiatan {$dharma} {$mulai}",
    ]);
    $r->prodi()->attach($prodi->id);

    return $r;
}

/** @return array<string, list<list<mixed>>> lembar => baris */
function bacaXlsx(string $path): array
{
    $reader = new Reader;
    $reader->open($path);
    $hasil = [];

    foreach ($reader->getSheetIterator() as $sheet) {
        foreach ($sheet->getRowIterator() as $row) {
            $hasil[$sheet->getName()][] = $row->toArray();
        }
    }

    $reader->close();

    return $hasil;
}

it('menghasilkan tiga lembar dengan kolom §9', function () {
    $path = tempnam(sys_get_temp_dir(), 'x').'.xlsx';
    (new EksporKerjaSamaTridharma)->tulis($this->prodi, 2025, $path);
    $lembar = bacaXlsx($path);
    unlink($path);

    expect(array_keys($lembar))->toBe(['Pendidikan', 'Penelitian', 'PkM'])
        ->and($lembar['Pendidikan'][0])->toBe(EksporKerjaSamaTridharma::KOLOM)
        ->and($lembar['Penelitian'][0])->toBe(EksporKerjaSamaTridharma::KOLOM)
        ->and($lembar['PkM'][0])->toBe(EksporKerjaSamaTridharma::KOLOM)
        ->and(EksporKerjaSamaTridharma::KOLOM)->toHaveCount(8);
});

it('mengelompokkan kegiatan terverifikasi per dharma dan hanya milik prodi serta rentang TS-2 s.d. TS', function () {
    $lain = Prodi::factory()->create();

    kegiatan($this->prodi, 'pendidikan', '2025-03-01');
    kegiatan($this->prodi, 'pendidikan', '2023-02-01');            // TS-2 → masuk
    kegiatan($this->prodi, 'pendidikan', '2022-12-31');            // sebelum rentang
    kegiatan($this->prodi, 'pendidikan', '2026-01-01');            // setelah rentang
    kegiatan($this->prodi, 'pendidikan', '2025-04-01', 'diajukan'); // belum terverifikasi
    kegiatan($this->prodi, 'pendidikan', '2025-05-01', 'draf');
    kegiatan($lain, 'pendidikan', '2025-06-01');                     // prodi lain
    kegiatan($this->prodi, 'penelitian', '2024-07-01');
    kegiatan($this->prodi, 'pkm', '2025-09-01');
    kegiatan($this->prodi, 'pkm', '2024-09-01');

    $ekspor = new EksporKerjaSamaTridharma;

    expect($ekspor->baris($this->prodi, 2025, Dharma::Pendidikan))->toHaveCount(2)
        ->and($ekspor->baris($this->prodi, 2025, Dharma::Penelitian))->toHaveCount(1)
        ->and($ekspor->baris($this->prodi, 2025, Dharma::Pkm))->toHaveCount(2);

    // urut tanggal mulai, bernomor urut
    $pendidikan = $ekspor->baris($this->prodi, 2025, Dharma::Pendidikan);

    expect($pendidikan[0][0])->toBe(1)->and($pendidikan[1][0])->toBe(2)
        ->and($pendidikan[0][3])->toBe('Kegiatan pendidikan 2023-02-01');
});

it('mengisi kolom baris dengan benar', function () {
    $sekolah = Mitra::factory()->create(['nama' => 'SMA Negeri 1']);
    $pt = Mitra::factory()->asing()->create(['nama' => 'Univ Asing']);
    $ks = KerjaSama::factory()->create([
        'nomor_dokumen_unsil' => '001/KS/2024',
        'tingkat' => 'internasional',
        'jenis_dokumen' => 'MoU',
        'tanggal_berakhir' => '2029-05-31',
        'berkas_dokumen' => 'kerja-sama/a.pdf',
    ]);
    $ks->mitra()->attach([$sekolah->id, $pt->id]);
    $r = kegiatan($this->prodi, 'pendidikan', '2025-08-25', ks: $ks, selesai: '2025-11-28');

    [$baris] = (new EksporKerjaSamaTridharma)->baris($this->prodi, 2025, Dharma::Pendidikan);

    expect($baris[0])->toBe(1)
        ->and($baris[1])->toBe('SMA Negeri 1; Univ Asing')
        ->and($baris[2])->toBe('Internasional')
        ->and($baris[3])->toBe($r->judul_kegiatan)
        ->and($baris[4])->toBe($r->manfaat_bagi_prodi)
        ->and($baris[5])->toBe('25/08/2025 – 28/11/2025 (96 hari)')
        ->and($baris[6])->toBe('MoU 001/KS/2024 — '.route('kerja-sama.berkas', [$ks, 'dokumen']))
        ->and($baris[7])->toBe(2029);
});

it('kegiatan tanpa tanggal selesai dan tanpa berkas tetap terekspor', function () {
    $ks = KerjaSama::factory()->create(['nomor_dokumen_unsil' => null, 'nomor_dokumen_mitra' => null, 'judul' => 'Judul KS']);
    kegiatan($this->prodi, 'penelitian', '2025-03-01', ks: $ks);

    [$baris] = (new EksporKerjaSamaTridharma)->baris($this->prodi, 2025, Dharma::Penelitian);

    expect($baris[5])->toBe('01/03/2025 (belum selesai)')
        ->and($baris[6])->toBe('MoA Judul KS');
});

it('mengunduh berkas xlsx lewat aksi di daftar realisasi', function (Peran $peran) {
    kegiatan($this->prodi, 'pendidikan', '2025-03-01');
    actingAs(User::factory()->create()->assignRole($peran->value));

    Livewire::test(ListRealisasiKegiatan::class)
        ->callAction('eksporTridharma', ['prodi_id' => $this->prodi->id, 'tahun_ts' => 2025])
        ->assertHasNoActionErrors()
        ->assertFileDownloaded('tabel-kerja-sama-tridharma-pmat-TS2025.xlsx');
})->with(Peran::cases());

it('aksi ekspor mewajibkan prodi dan tahun TS', function () {
    actingAs(User::factory()->create()->assignRole(Peran::Pimpinan->value));

    Livewire::test(ListRealisasiKegiatan::class)
        ->callAction('eksporTridharma', ['prodi_id' => null, 'tahun_ts' => null])
        ->assertHasActionErrors(['prodi_id' => 'required', 'tahun_ts' => 'required']);
});
