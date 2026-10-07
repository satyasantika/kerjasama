<?php

use App\Enums\Peran;
use App\Filament\Resources\BentukKerjaSama\Pages\ListBentukKerjaSama;
use App\Filament\Resources\Mitra\Pages\CreateMitra;
use App\Filament\Resources\Mitra\Pages\ListMitra;
use App\Filament\Resources\Prodi\Pages\CreateProdi;
use App\Filament\Resources\Prodi\Pages\EditProdi;
use App\Filament\Resources\Prodi\Pages\ListProdi;
use App\Filament\Resources\Prodi\RelationManagers\NdtpsRelationManager;
use App\Models\BentukKerjaSama;
use App\Models\Mitra;
use App\Models\Prodi;
use App\Models\ProdiNdtps;
use App\Models\User;
use Database\Seeders\BentukKerjaSamaSeeder;
use Database\Seeders\ProdiSeeder;
use Database\Seeders\RolSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(RolSeeder::class);
    Filament::setCurrentPanel('admin');
});

function userDengan(Peran $peran): User
{
    return User::factory()->create()->assignRole($peran->value);
}

it('seeder bentuk kerja sama berisi 41 baris dan idempoten', function () {
    $this->seed(BentukKerjaSamaSeeder::class);
    $this->seed(BentukKerjaSamaSeeder::class);

    expect(BentukKerjaSama::count())->toBe(41)
        ->and(BentukKerjaSama::where('kode', 'AK-PT-05')->value('nama'))->toBe('Program kembaran')
        ->and(BentukKerjaSama::where('kode', 'FKIP-01')->first()->dharma_default->value)->toBe('pendidikan')
        ->and(BentukKerjaSama::whereNull('dharma_default')->count())->toBeGreaterThan(0);
});

it('mengizinkan peran kerja sama melihat master', function (Peran $peran) {
    actingAs(userDengan($peran));

    Livewire::test(ListProdi::class)->assertSuccessful();
    Livewire::test(ListMitra::class)->assertSuccessful();
    Livewire::test(ListBentukKerjaSama::class)->assertSuccessful();
})->with([Peran::AdminFakultas, Peran::AdminProdi, Peran::Pimpinan]);

it('hanya admin_fakultas yang boleh mengubah master', function (Peran $peran, bool $boleh) {
    $user = userDengan($peran);

    expect($user->can('create', Prodi::class))->toBe($boleh)
        ->and($user->can('create', Mitra::class))->toBe($boleh)
        ->and($user->can('create', BentukKerjaSama::class))->toBe($boleh)
        ->and($user->can('update', new Prodi))->toBe($boleh);
})->with([
    [Peran::SuperAdmin, false],
    [Peran::AdminFakultas, true],
    [Peran::AdminProdi, false],
    [Peran::Pimpinan, false],
]);

it('tidak pernah mengizinkan hapus permanen', function () {
    $user = userDengan(Peran::SuperAdmin);

    expect($user->can('forceDelete', new Mitra))->toBeFalse()
        ->and($user->can('delete', new Prodi))->toBeFalse()
        ->and($user->can('delete', new BentukKerjaSama))->toBeFalse();
});

it('membuat prodi lewat form dan menolak kode ganda', function () {
    actingAs(userDengan(Peran::AdminFakultas));

    Livewire::test(CreateProdi::class)
        ->fillForm(['kode' => 'PMAT', 'nama' => 'Pendidikan Matematika', 'jenjang' => 'S1', 'aktif' => true])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Prodi::where('kode', 'PMAT')->exists())->toBeTrue();

    Livewire::test(CreateProdi::class)
        ->fillForm(['kode' => 'PMAT', 'nama' => 'Lain', 'jenjang' => 'S1'])
        ->call('create')
        ->assertHasFormErrors(['kode' => 'unique']);
});

it('admin_prodi tidak bisa membuka form tambah prodi', function () {
    actingAs(userDengan(Peran::AdminProdi));

    Livewire::test(CreateProdi::class)->assertForbidden();
});

it('mitra unik per kombinasi nama dan negara', function () {
    actingAs(userDengan(Peran::AdminFakultas));

    Mitra::create(['nama' => 'Universitas X', 'jenis' => 'perguruan_tinggi', 'negara' => 'Indonesia']);

    Livewire::test(CreateMitra::class)
        ->fillForm(['nama' => 'Universitas X', 'jenis' => 'perguruan_tinggi', 'negara' => 'Indonesia'])
        ->call('create')
        ->assertHasFormErrors(['nama' => 'unique']);

    Livewire::test(CreateMitra::class)
        ->fillForm(['nama' => 'Universitas X', 'jenis' => 'perguruan_tinggi', 'negara' => 'Malaysia', 'status_legal' => 'Terakreditasi'])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('mitra memakai soft delete dan mengenali mitra asing', function () {
    $mitra = Mitra::create(['nama' => 'Univ Y', 'jenis' => 'perguruan_tinggi', 'negara' => 'Malaysia']);

    expect($mitra->asing)->toBeTrue();

    $mitra->delete();

    expect(Mitra::count())->toBe(0)->and(Mitra::withTrashed()->count())->toBe(1);
});

it('NDTPS unik per prodi dan tahun', function () {
    $prodi = Prodi::create(['kode' => 'PMAT', 'nama' => 'Pendidikan Matematika', 'jenjang' => 'S1']);
    actingAs(userDengan(Peran::AdminFakultas));

    Livewire::test(NdtpsRelationManager::class, ['ownerRecord' => $prodi, 'pageClass' => EditProdi::class])
        ->callAction(TestAction::make('create')->table(), ['tahun_ts' => 2025, 'ndtps' => 12])
        ->assertHasNoFormErrors();

    expect(ProdiNdtps::where('prodi_id', $prodi->id)->where('tahun_ts', 2025)->value('ndtps'))->toBe(12);

    Livewire::test(NdtpsRelationManager::class, ['ownerRecord' => $prodi, 'pageClass' => EditProdi::class])
        ->callAction(TestAction::make('create')->table(), ['tahun_ts' => 2025, 'ndtps' => 10])
        ->assertHasFormErrors(['tahun_ts' => 'unique']);
});

it('user bisa dikaitkan ke prodi', function () {
    $prodi = Prodi::create(['kode' => 'PMAT', 'nama' => 'Pendidikan Matematika', 'jenjang' => 'S1']);
    $user = User::factory()->create(['prodi_id' => $prodi->id]);

    expect($user->fresh()->prodi_id)->toBe($prodi->id);
});

it('seeder prodi berisi 13 prodi, idempoten, dan tidak menimpa perubahan manual', function () {
    $this->seed(ProdiSeeder::class);
    Prodi::where('kode', 'PMAT')->update(['nama' => 'Diubah manual']);
    $this->seed(ProdiSeeder::class);

    expect(Prodi::count())->toBe(13)
        ->and(Prodi::where('kode', 'PPG')->first()->jenjang->value)->toBe('PPG')
        ->and(Prodi::where('jenjang', 'S1')->count())->toBe(12)
        ->and(Prodi::where('kode', 'PMAT')->value('nama'))->toBe('Diubah manual');
});

it('migrate:fresh --seed menyertakan prodi', function () {
    $this->seed();

    expect(Prodi::count())->toBe(13);
});
