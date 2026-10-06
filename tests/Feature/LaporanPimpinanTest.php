<?php

use App\Enums\Peran;
use App\Filament\Pages\Dashboard;
use App\Models\KerjaSama;
use App\Models\Mitra;
use App\Models\Prodi;
use App\Models\User;
use App\Services\RekapPimpinan;
use Database\Seeders\RolSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed(RolSeeder::class);
    Filament::setCurrentPanel('admin');
});

function ksRentang(int $mulai, int $berakhir, array $extra = []): KerjaSama
{
    return KerjaSama::factory()->berlaku(today()->addDays($mulai)->toDateString(), today()->addDays($berakhir)->toDateString())->create($extra);
}

it('rekap memuat angka status, tingkat, jenis mitra, dan daftar', function () {
    $mitra = Mitra::factory()->create(['jenis' => 'sekolah']);
    ksRentang(-100, 400);
    $dekat = ksRentang(-100, 20, ['judul' => 'Hampir Habis', 'tingkat' => 'nasional']);
    $dekat->mitra()->attach($mitra->id);
    $dekat->prodi()->attach(Prodi::factory()->create(['nama' => 'Pendidikan Fisika'])->id, ['penginisiasi' => false]);
    $lewat = ksRentang(-400, -5, ['judul' => 'Sudah Lewat']);

    $data = (new RekapPimpinan)->data();

    expect($data['status'])->toBe(['Aktif' => 1, 'Akan berakhir' => 1, 'Kedaluwarsa' => 1, 'Belum berlaku' => 0])
        ->and($data['perTingkat'])->toBe(['Lokal' => 1, 'Nasional' => 1, 'Internasional' => 0])
        ->and($data['perJenisMitra']['Sekolah'])->toBe(1)
        ->and($data['akanBerakhir']->pluck('judul')->all())->toBe(['Hampir Habis'])
        ->and($data['kedaluwarsa']->pluck('id')->all())->toBe([$lewat->id]);
});

it('semua peran dapat mengunduh PDF rekap', function (Peran $peran) {
    ksRentang(-100, 20, ['judul' => 'Hampir Habis']);
    actingAs(User::factory()->create()->assignRole($peran->value));

    $respons = get(route('laporan.rekap-pimpinan'))->assertOk();

    expect($respons->headers->get('content-type'))->toBe('application/pdf')
        ->and($respons->headers->get('content-disposition'))->toContain('rekap-kerja-sama-'.now()->format('Y-m-d').'.pdf')
        ->and(substr($respons->getContent(), 0, 5))->toBe('%PDF-');
})->with(Peran::cases());

it('tamu dialihkan ke login dan pengguna tanpa peran ditolak', function () {
    get(route('laporan.rekap-pimpinan'))->assertRedirect(route('filament.admin.auth.login'));

    actingAs(User::factory()->create());
    get(route('laporan.rekap-pimpinan'))->assertForbidden();
});

it('PDF tetap terbentuk saat tidak ada data', function () {
    actingAs(User::factory()->create()->assignRole(Peran::Pimpinan->value));

    get(route('laporan.rekap-pimpinan'))->assertOk();
});

it('dashboard punya tombol unduh rekap PDF', function () {
    actingAs(User::factory()->create()->assignRole(Peran::Pimpinan->value));

    Livewire::test(Dashboard::class)->assertActionVisible('laporanPdf');
});
