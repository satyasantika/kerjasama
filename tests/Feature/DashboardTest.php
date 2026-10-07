<?php

use App\Enums\Peran;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\KerjaSamaAkanBerakhirTable;
use App\Filament\Widgets\KerjaSamaPerJenisMitraChart;
use App\Filament\Widgets\KerjaSamaPerTingkatChart;
use App\Filament\Widgets\RingkasanKerjaSama;
use App\Models\KerjaSama;
use App\Models\Mitra;
use App\Models\User;
use Database\Seeders\RolSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed(RolSeeder::class);
    Filament::setCurrentPanel('admin');
    actingAs(User::factory()->create()->assignRole(Peran::Pimpinan->value));
});

function ksTanggal(int $mulaiOffset, int $berakhirOffset, array $extra = []): KerjaSama
{
    return KerjaSama::factory()->berlaku(today()->addDays($mulaiOffset)->toDateString(), today()->addDays($berakhirOffset)->toDateString())->create($extra);
}

it('dashboard menampilkan widget kerja sama untuk peran yang mengurus kerja sama', function (Peran $peran) {
    actingAs(User::factory()->create()->assignRole($peran->value));

    get('/admin')->assertOk();

    Livewire::test(Dashboard::class)
        ->assertSeeLivewire(RingkasanKerjaSama::class)
        ->assertSeeLivewire(KerjaSamaPerTingkatChart::class)
        ->assertSeeLivewire(KerjaSamaPerJenisMitraChart::class)
        ->assertSeeLivewire(KerjaSamaAkanBerakhirTable::class);
})->with([Peran::AdminFakultas, Peran::AdminProdi, Peran::Pimpinan]);

it('kartu menghitung aktif, akan berakhir, dan kedaluwarsa', function () {
    ksTanggal(-100, 400);                                        // aktif
    ksTanggal(-100, 500);                                        // aktif
    ksTanggal(-100, 30);                                         // akan berakhir
    ksTanggal(-400, -1);                                         // kedaluwarsa
    ksTanggal(-100, 400, ['status_manual' => 'dibatalkan']);     // tidak dihitung
    ksTanggal(10, 400);                                          // belum berlaku, tidak dihitung

    Livewire::test(RingkasanKerjaSama::class)
        ->assertSeeInOrder(['Aktif', '2', 'Akan berakhir', '1', 'Kedaluwarsa', '1']);
});

it('grafik per tingkat hanya menghitung kerja sama yang sedang berlaku', function () {
    ksTanggal(-100, 400, ['tingkat' => 'lokal']);
    ksTanggal(-100, 30, ['tingkat' => 'lokal']);
    ksTanggal(-100, 400, ['tingkat' => 'internasional']);
    ksTanggal(-400, -1, ['tingkat' => 'nasional']);              // kedaluwarsa, tidak dihitung

    $data = Livewire::test(KerjaSamaPerTingkatChart::class)->instance();
    $data = (fn () => $this->getData())->call($data);

    expect($data['labels'])->toBe(['Lokal', 'Nasional', 'Internasional'])
        ->and($data['datasets'][0]['data'])->toBe([2, 0, 1]);
});

it('grafik per jenis mitra menghitung tiap kerja sama sekali per jenis', function () {
    $sekolah1 = Mitra::factory()->create(['jenis' => 'sekolah']);
    $sekolah2 = Mitra::factory()->create(['jenis' => 'sekolah']);
    $pt = Mitra::factory()->asing()->create();

    ksTanggal(-100, 400)->mitra()->attach([$sekolah1->id, $sekolah2->id]);  // 1 untuk sekolah
    ksTanggal(-100, 400)->mitra()->attach([$sekolah1->id, $pt->id]);         // sekolah + PT
    ksTanggal(-400, -1)->mitra()->attach([$pt->id]);                         // kedaluwarsa

    $data = Livewire::test(KerjaSamaPerJenisMitraChart::class)->instance();
    $data = (fn () => $this->getData())->call($data);
    $peta = array_combine($data['labels'], $data['datasets'][0]['data']);

    expect($peta['Sekolah'])->toBe(2)
        ->and($peta['Perguruan Tinggi'])->toBe(1)
        ->and($peta['Pemerintah'])->toBe(0);
});

it('tabel menampilkan hanya kerja sama yang akan berakhir diurut berdasarkan tanggal', function () {
    $dekat = ksTanggal(-100, 10);
    $jauh = ksTanggal(-100, 80);
    $aktif = ksTanggal(-100, 400);
    $lewat = ksTanggal(-400, -1);

    Livewire::test(KerjaSamaAkanBerakhirTable::class)
        ->assertCanSeeTableRecords([$dekat, $jauh], inOrder: true)
        ->assertCanNotSeeTableRecords([$aktif, $lewat]);
});

it('widget tidak tampil untuk pengguna tanpa peran', function () {
    actingAs(User::factory()->create());

    expect(RingkasanKerjaSama::canView())->toBeFalse()
        ->and(KerjaSamaAkanBerakhirTable::canView())->toBeFalse();
});
