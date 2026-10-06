<?php

use App\Enums\Peran;
use App\Enums\StatusKerjaSama;
use App\Filament\Resources\KerjaSama\Pages\CreateKerjaSama;
use App\Filament\Resources\KerjaSama\Pages\EditKerjaSama;
use App\Filament\Resources\KerjaSama\Pages\ListKerjaSama;
use App\Filament\Resources\KerjaSama\Pages\ViewKerjaSama;
use App\Models\KerjaSama;
use App\Models\Mitra;
use App\Models\Prodi;
use App\Models\User;
use Database\Seeders\RolSeeder;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed(RolSeeder::class);
    Filament::setCurrentPanel('admin');
});

function adminProdi(Prodi $prodi): User
{
    return User::factory()->create(['prodi_id' => $prodi->id])->assignRole(Peran::AdminProdi->value);
}

function peranUser(Peran $peran): User
{
    return User::factory()->create()->assignRole($peran->value);
}

function ksDenganProdi(Prodi ...$prodi): KerjaSama
{
    $ks = KerjaSama::factory()->create();
    foreach ($prodi as $p) {
        $ks->prodi()->attach($p->id, ['penginisiasi' => false]);
    }

    return $ks;
}

it('scope status konsisten dengan accessor status', function () {
    $kasus = [
        StatusKerjaSama::BelumBerlaku->value => KerjaSama::factory()->berlaku(today()->addDay()->toDateString(), today()->addYears(3)->toDateString())->create(),
        StatusKerjaSama::Kedaluwarsa->value => KerjaSama::factory()->berlaku(today()->subYears(3)->toDateString(), today()->subDay()->toDateString())->create(),
        StatusKerjaSama::AkanBerakhir->value => KerjaSama::factory()->berlaku(today()->subYear()->toDateString(), today()->addDays(90)->toDateString())->create(),
        StatusKerjaSama::Aktif->value => KerjaSama::factory()->berlaku(today()->subYear()->toDateString(), today()->addDays(91)->toDateString())->create(),
        StatusKerjaSama::Dibatalkan->value => KerjaSama::factory()->create(['status_manual' => 'dibatalkan']),
        StatusKerjaSama::Dihentikan->value => KerjaSama::factory()->create(['status_manual' => 'dihentikan']),
    ];

    foreach ($kasus as $nilai => $ks) {
        $status = StatusKerjaSama::from($nilai);

        expect($ks->status)->toBe($status)
            ->and(KerjaSama::status($status)->pluck('id')->all())->toBe([$ks->id]);
    }

    expect(KerjaSama::aktif()->count())->toBe(1)
        ->and(KerjaSama::akanBerakhir()->count())->toBe(1)
        ->and(KerjaSama::kedaluwarsa()->count())->toBe(1)
        ->and(KerjaSama::belumBerlaku()->count())->toBe(1);
});

it('admin_prodi hanya boleh mengubah kerja sama yang melibatkan prodinya', function () {
    $prodiA = Prodi::factory()->create();
    $prodiB = Prodi::factory()->create();
    $user = adminProdi($prodiA);

    $milikA = ksDenganProdi($prodiA);
    $milikB = ksDenganProdi($prodiB);
    $bersama = ksDenganProdi($prodiA, $prodiB);
    $tanpaProdi = ksDenganProdi();

    expect($user->can('update', $milikA))->toBeTrue()
        ->and($user->can('update', $bersama))->toBeTrue()
        ->and($user->can('update', $milikB))->toBeFalse()
        ->and($user->can('update', $tanpaProdi))->toBeFalse()
        ->and($user->can('view', $milikB))->toBeTrue()
        ->and($user->can('delete', $milikA))->toBeFalse()
        ->and($user->can('forceDelete', $milikA))->toBeFalse();

    actingAs($user);
    Livewire::test(EditKerjaSama::class, ['record' => $milikB->getRouteKey()])->assertForbidden();
    Livewire::test(EditKerjaSama::class, ['record' => $milikA->getRouteKey()])->assertSuccessful();
});

it('admin_fakultas dan super_admin boleh mengubah dan menghapus (soft) semua', function (Peran $peran) {
    $user = peranUser($peran);
    $ks = ksDenganProdi(Prodi::factory()->create());

    expect($user->can('update', $ks))->toBeTrue()
        ->and($user->can('delete', $ks))->toBeTrue()
        ->and($user->can('forceDelete', $ks))->toBeFalse();
})->with([Peran::SuperAdmin, Peran::AdminFakultas]);

it('pimpinan hanya bisa melihat', function () {
    $user = peranUser(Peran::Pimpinan);
    $ks = KerjaSama::factory()->create();

    expect($user->can('viewAny', KerjaSama::class))->toBeTrue()
        ->and($user->can('view', $ks))->toBeTrue()
        ->and($user->can('create', KerjaSama::class))->toBeFalse()
        ->and($user->can('update', $ks))->toBeFalse();

    actingAs($user);
    Livewire::test(ViewKerjaSama::class, ['record' => $ks->getRouteKey()])->assertSuccessful();
    Livewire::test(CreateKerjaSama::class)->assertForbidden();
});

it('admin_prodi tanpa prodi tidak bisa membuat kerja sama', function () {
    $user = User::factory()->create()->assignRole(Peran::AdminProdi->value);

    expect($user->can('create', KerjaSama::class))->toBeFalse();
});

it('membuat kerja sama lewat form dan prodi admin_prodi ikut disertakan', function () {
    $prodiA = Prodi::factory()->create();
    $mitra = Mitra::factory()->create();
    actingAs(adminProdi($prodiA));

    Livewire::test(CreateKerjaSama::class)
        ->fillForm([
            'jenis_dokumen' => 'MoA',
            'judul' => 'Kerja Sama PLP',
            'ruang_lingkup' => 'Penempatan PLP',
            'bidang' => 'akademik',
            'tingkat' => 'lokal',
            'mitra' => [$mitra->id],
            'prodi_ids' => [],
            'pihak_penandatangan_unsil' => 'dekan',
            'tanggal_tanda_tangan' => '2026-01-10',
            'tanggal_mulai' => '2026-01-10',
            'tanggal_berakhir' => '2029-01-09',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $ks = KerjaSama::firstWhere('judul', 'Kerja Sama PLP');

    expect($ks)->not->toBeNull()
        ->and($ks->prodi->pluck('id')->all())->toBe([$prodiA->id])
        ->and($ks->mitra->pluck('id')->all())->toBe([$mitra->id])
        ->and($ks->dibuat_oleh)->not->toBeNull();
});

it('menolak tanggal berakhir yang tidak setelah tanggal mulai', function () {
    $mitra = Mitra::factory()->create();
    actingAs(peranUser(Peran::AdminFakultas));

    Livewire::test(CreateKerjaSama::class)
        ->fillForm([
            'jenis_dokumen' => 'MoU', 'judul' => 'X', 'ruang_lingkup' => 'Y', 'bidang' => 'akademik',
            'tingkat' => 'lokal', 'mitra' => [$mitra->id], 'pihak_penandatangan_unsil' => 'dekan',
            'tanggal_tanda_tangan' => '2026-01-10', 'tanggal_mulai' => '2026-01-10', 'tanggal_berakhir' => '2026-01-10',
        ])
        ->call('create')
        ->assertHasFormErrors(['tanggal_berakhir']);
});

it('mitra asing otomatis menjadikan kerja sama internasional dan perlu persetujuan Dirjen', function () {
    $asing = Mitra::factory()->asing()->create();
    actingAs(peranUser(Peran::AdminFakultas));

    Livewire::test(CreateKerjaSama::class)
        ->fillForm([
            'jenis_dokumen' => 'MoU', 'judul' => 'Kerja Sama Luar Negeri', 'ruang_lingkup' => 'Y', 'bidang' => 'akademik',
            'tingkat' => 'lokal', 'mitra' => [$asing->id], 'pihak_penandatangan_unsil' => 'rektor',
            'tanggal_tanda_tangan' => '2026-01-10', 'tanggal_mulai' => '2026-01-10', 'tanggal_berakhir' => '2029-01-09',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $ks = KerjaSama::firstWhere('judul', 'Kerja Sama Luar Negeri');

    expect($ks->tingkat->value)->toBe('internasional')
        ->and($ks->perlu_persetujuan_dirjen)->toBeTrue();
});

it('admin_fakultas dapat menghapus (soft) dan memulihkan kerja sama', function () {
    actingAs(peranUser(Peran::AdminFakultas));
    $ks = KerjaSama::factory()->create();

    Livewire::test(EditKerjaSama::class, ['record' => $ks->getRouteKey()])
        ->callAction('delete');

    expect(KerjaSama::count())->toBe(0)->and(KerjaSama::withTrashed()->count())->toBe(1);
});

it('daftar kerja sama menyaring berdasarkan status, prodi, dan jenis mitra', function () {
    actingAs(peranUser(Peran::AdminFakultas));
    $prodi = Prodi::factory()->create();
    $univ = Mitra::factory()->asing()->create();
    $sekolah = Mitra::factory()->create();

    $a = ksDenganProdi($prodi);
    $a->mitra()->attach($univ);
    $b = KerjaSama::factory()->berlaku(today()->subYears(3)->toDateString(), today()->subDay()->toDateString())->create();
    $b->mitra()->attach($sekolah);

    Livewire::test(ListKerjaSama::class)
        ->assertCanSeeTableRecords([$a, $b])
        ->filterTable('status', 'kedaluwarsa')
        ->assertCanSeeTableRecords([$b])->assertCanNotSeeTableRecords([$a])
        ->resetTableFilters()
        ->filterTable('prodi', $prodi->id)
        ->assertCanSeeTableRecords([$a])->assertCanNotSeeTableRecords([$b])
        ->resetTableFilters()
        ->filterTable('jenis_mitra', 'sekolah')
        ->assertCanSeeTableRecords([$b])->assertCanNotSeeTableRecords([$a]);
});

it('berkas PDF tidak bisa diunduh tanpa login', function () {
    Storage::fake('local');
    Storage::disk('local')->put('kerja-sama/a.pdf', '%PDF-1.4 contoh');
    $ks = KerjaSama::factory()->create(['berkas_dokumen' => 'kerja-sama/a.pdf']);

    get(route('kerja-sama.berkas', [$ks, 'dokumen']))->assertRedirect(route('filament.admin.auth.login'));
});

it('berkas PDF bisa diunduh oleh pengguna berperan dan 404 bila tidak ada', function () {
    Storage::fake('local');
    Storage::disk('local')->put('kerja-sama/a.pdf', '%PDF-1.4 contoh');
    $ada = KerjaSama::factory()->create(['berkas_dokumen' => 'kerja-sama/a.pdf']);
    $kosong = KerjaSama::factory()->create();

    actingAs(peranUser(Peran::Pimpinan));

    get(route('kerja-sama.berkas', [$ada, 'dokumen']))->assertOk()->assertDownload();
    get(route('kerja-sama.berkas', [$kosong, 'dokumen']))->assertNotFound();
    get(route('kerja-sama.berkas', [$ada, 'asing']))->assertNotFound();
});

it('berkas PDF ditolak untuk pengguna tanpa peran', function () {
    Storage::fake('local');
    Storage::disk('local')->put('kerja-sama/a.pdf', '%PDF-1.4 contoh');
    $ks = KerjaSama::factory()->create(['berkas_dokumen' => 'kerja-sama/a.pdf']);

    actingAs(User::factory()->create());

    get(route('kerja-sama.berkas', [$ks, 'dokumen']))->assertForbidden();
});

it('dokumen induk dan turunan saling terhubung', function () {
    $induk = KerjaSama::factory()->create(['jenis_dokumen' => 'MoU']);
    $anak = KerjaSama::factory()->create(['induk_id' => $induk->id, 'jenis_dokumen' => 'IA']);

    expect($induk->anak->pluck('id')->all())->toBe([$anak->id])
        ->and($anak->induk->is($induk))->toBeTrue();
});

it('mitra asing wajib mengisi status legal', function () {
    actingAs(peranUser(Peran::AdminFakultas));

    Livewire::test(\App\Filament\Resources\Mitra\Pages\CreateMitra::class)
        ->fillForm(['nama' => 'Univ Z', 'jenis' => 'perguruan_tinggi', 'negara' => 'Malaysia'])
        ->call('create')
        ->assertHasFormErrors(['status_legal' => 'required']);
});
