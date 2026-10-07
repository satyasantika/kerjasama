<?php

use App\Enums\Peran;
use App\Filament\Pages\SimulasiSkorLamdik;
use App\Models\EvaluasiKerjaSama;
use App\Models\KerjaSama;
use App\Models\Prodi;
use App\Models\ProdiNdtps;
use App\Models\RealisasiKegiatan;
use App\Models\User;
use App\Services\RekapKerjaSamaProdi;
use Database\Seeders\RolSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(RolSeeder::class);
    Filament::setCurrentPanel('admin');
    $this->prodi = Prodi::factory()->create();
});

function realisasiTerverifikasi(Prodi $prodi, string $dharma, string $tingkat, string $mulai = '2025-03-01'): void
{
    $ks = KerjaSama::factory()->create(['tingkat' => $tingkat]);
    $ks->prodi()->attach($prodi->id, ['penginisiasi' => false]);
    $r = RealisasiKegiatan::factory()->status('terverifikasi')->create([
        'kerja_sama_id' => $ks->id, 'dharma' => $dharma, 'tanggal_mulai' => $mulai,
    ]);
    $r->prodi()->attach($prodi->id);
}

it('merekap N1–N3 dan NI/NN/NW dari realisasi terverifikasi prodi pada TS-2 s.d. TS', function () {
    realisasiTerverifikasi($this->prodi, 'pendidikan', 'lokal');
    realisasiTerverifikasi($this->prodi, 'pendidikan', 'internasional');
    realisasiTerverifikasi($this->prodi, 'penelitian', 'nasional', '2023-06-01');
    realisasiTerverifikasi($this->prodi, 'pkm', 'lokal');
    realisasiTerverifikasi($this->prodi, 'pkm', 'lokal', '2022-06-01');   // di luar rentang
    realisasiTerverifikasi(Prodi::factory()->create(), 'pkm', 'lokal');   // prodi lain

    expect((new RekapKerjaSamaProdi)->jumlah($this->prodi, 2025))
        ->toBe(['n1' => 2, 'n2' => 1, 'n3' => 1, 'ni' => 1, 'nn' => 1, 'nw' => 2]);
});

it('mengambil NDTPS dari tahun TS dan skor (b) dari rata-rata evaluasi', function () {
    ProdiNdtps::create(['prodi_id' => $this->prodi->id, 'tahun_ts' => 2025, 'ndtps' => 12]);
    $ks = KerjaSama::factory()->create();
    $ks->prodi()->attach($this->prodi->id, ['penginisiasi' => false]);
    EvaluasiKerjaSama::factory()->create(['kerja_sama_id' => $ks->id, 'tahun_ts' => 2025, 'skor_keefektifan' => 4]);
    EvaluasiKerjaSama::factory()->create(['kerja_sama_id' => $ks->id, 'tahun_ts' => 2024, 'skor_keefektifan' => 3]);
    EvaluasiKerjaSama::factory()->create(['kerja_sama_id' => $ks->id, 'tahun_ts' => 2020, 'skor_keefektifan' => 1]); // di luar rentang

    $rekap = new RekapKerjaSamaProdi;

    expect($rekap->ndtps($this->prodi, 2025))->toBe(12)
        ->and($rekap->ndtps($this->prodi, 2024))->toBeNull()
        ->and($rekap->skorB($this->prodi, 2025))->toBe(3.5)
        ->and($rekap->skorB(Prodi::factory()->create(), 2025))->toBeNull();
});

it('halaman simulasi dapat dibuka peran kerja sama dan menghitung dari isian manual', function (Peran $peran) {
    actingAs(User::factory()->create()->assignRole($peran->value));

    $page = Livewire::test(SimulasiSkorLamdik::class)
        ->fillForm(['n1' => 4, 'n2' => 2, 'n3' => 3, 'ndtps' => 6, 'ni' => 1, 'nn' => 3, 'nw' => 10, 'skor_b' => 3])
        ->call('hitung');

    expect($page->get('hasil')['skor'])->toEqualWithDelta(3.1458, 0.00005)
        ->and($page->get('pesan'))->toBeNull();
})->with([Peran::AdminFakultas, Peran::AdminProdi, Peran::Pimpinan]);

it('menampilkan pesan, bukan error, bila NDTPS nol', function () {
    actingAs(User::factory()->create()->assignRole(Peran::Pimpinan->value));

    $page = Livewire::test(SimulasiSkorLamdik::class)
        ->fillForm(['n1' => 1, 'ndtps' => 0, 'skor_b' => 2])
        ->call('hitung');

    expect($page->get('hasil'))->toBeNull()
        ->and($page->get('pesan'))->toContain('NDTPS bernilai 0');
});

it('tombol ambil data mengisi angka dari sistem', function () {
    realisasiTerverifikasi($this->prodi, 'pendidikan', 'lokal');
    realisasiTerverifikasi($this->prodi, 'penelitian', 'internasional');
    ProdiNdtps::create(['prodi_id' => $this->prodi->id, 'tahun_ts' => 2025, 'ndtps' => 5]);
    actingAs(User::factory()->create()->assignRole(Peran::AdminFakultas->value));

    $page = Livewire::test(SimulasiSkorLamdik::class)
        ->fillForm(['prodi_id' => $this->prodi->id, 'tahun_ts' => 2025])
        ->call('ambilData')
        ->assertFormSet(['n1' => 1, 'n2' => 1, 'n3' => 0, 'ni' => 1, 'nw' => 1, 'ndtps' => 5]);

    // RK = (3·1 + 2·1) / 5 = 1; B: NI=1<2, NN=0 → rumus umum 3; skor(a) = (2·1+3)/3
    expect((float) $page->get('hasil')['rk'])->toBe(1.0)->and((float) $page->get('hasil')['b'])->toBe(3.0);
});

it('ambil data tanpa memilih prodi memberi pesan', function () {
    actingAs(User::factory()->create()->assignRole(Peran::AdminFakultas->value));

    Livewire::test(SimulasiSkorLamdik::class)->fillForm(['prodi_id' => null])->call('ambilData')
        ->assertSet('pesan', 'Pilih program studi terlebih dahulu.');
});

it('tidak dapat diakses pengguna tanpa peran', function () {
    actingAs(User::factory()->create());

    expect(SimulasiSkorLamdik::canAccess())->toBeFalse();
});
