<?php

use App\Enums\Peran;
use App\Enums\StatusVerifikasi;
use App\Filament\Resources\KerjaSama\Pages\ViewKerjaSama;
use App\Filament\Resources\KerjaSama\RelationManagers\RealisasiRelationManager;
use App\Filament\Resources\RealisasiKegiatan\Pages\CreateRealisasiKegiatan;
use App\Filament\Resources\RealisasiKegiatan\Pages\EditRealisasiKegiatan;
use App\Filament\Resources\RealisasiKegiatan\Pages\ListRealisasiKegiatan;
use App\Filament\Resources\RealisasiKegiatan\Pages\ViewRealisasiKegiatan;
use App\Filament\Resources\RealisasiKegiatan\RelationManagers\BerkasBuktiRelationManager;
use App\Models\BentukKerjaSama;
use App\Models\BerkasBukti;
use App\Models\KerjaSama;
use App\Models\Prodi;
use App\Models\RealisasiKegiatan;
use App\Models\User;
use Database\Seeders\BentukKerjaSamaSeeder;
use Database\Seeders\RolSeeder;
use Filament\Actions\Testing\TestAction;
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

function pengusul(Prodi $prodi): User
{
    return User::factory()->create(['prodi_id' => $prodi->id])->assignRole(Peran::AdminProdi->value);
}

function userPeran(Peran $peran): User
{
    return User::factory()->create()->assignRole($peran->value);
}

function realisasiUntuk(Prodi $prodi, string $status = 'draf'): RealisasiKegiatan
{
    $r = RealisasiKegiatan::factory()->status($status)->create();
    $r->prodi()->attach($prodi->id);

    return $r;
}

it('alur lengkap: ajukan oleh admin_prodi lalu verifikasi oleh admin_fakultas', function () {
    Storage::fake('local');
    $prodi = Prodi::factory()->create();
    $r = realisasiUntuk($prodi);
    BerkasBukti::factory()->create(['realisasi_kegiatan_id' => $r->id, 'jenis' => 'laporan']);

    actingAs(pengusul($prodi));
    Livewire::test(ViewRealisasiKegiatan::class, ['record' => $r->getRouteKey()])
        ->assertActionVisible('ajukan')
        ->assertActionHidden('verifikasi')
        ->callAction('ajukan');

    expect($r->fresh()->status_verifikasi)->toBe(StatusVerifikasi::Diajukan);

    $fakultas = userPeran(Peran::AdminFakultas);
    actingAs($fakultas);
    Livewire::test(ViewRealisasiKegiatan::class, ['record' => $r->getRouteKey()])
        ->assertActionVisible('verifikasi')
        ->callAction('verifikasi');

    $r->refresh();

    expect($r->status_verifikasi)->toBe(StatusVerifikasi::Terverifikasi)
        ->and($r->diverifikasi_oleh)->toBe($fakultas->id)
        ->and($r->diverifikasi_pada)->not->toBeNull();
});

it('tidak bisa diverifikasi tanpa bukti berjenis laporan', function () {
    $prodi = Prodi::factory()->create();
    $r = realisasiUntuk($prodi, 'diajukan');
    BerkasBukti::factory()->create(['realisasi_kegiatan_id' => $r->id, 'jenis' => 'foto']);

    actingAs(userPeran(Peran::AdminFakultas));
    Livewire::test(ViewRealisasiKegiatan::class, ['record' => $r->getRouteKey()])->callAction('verifikasi');

    expect($r->fresh()->status_verifikasi)->toBe(StatusVerifikasi::Diajukan);

    expect(fn () => $r->verifikasi(userPeran(Peran::AdminFakultas)))->toThrow(DomainException::class);
});

it('admin_fakultas dapat menolak dengan catatan lalu pengusul mengajukan ulang', function () {
    $prodi = Prodi::factory()->create();
    $r = realisasiUntuk($prodi, 'diajukan');

    actingAs(userPeran(Peran::AdminFakultas));
    Livewire::test(ViewRealisasiKegiatan::class, ['record' => $r->getRouteKey()])
        ->callAction('tolak', ['catatan' => 'Laporan belum lengkap']);

    $r->refresh();

    expect($r->status_verifikasi)->toBe(StatusVerifikasi::Ditolak)
        ->and($r->catatan_verifikasi)->toBe('Laporan belum lengkap');

    actingAs(pengusul($prodi));
    Livewire::test(ViewRealisasiKegiatan::class, ['record' => $r->getRouteKey()])->callAction('ajukan');

    $r->refresh();

    expect($r->status_verifikasi)->toBe(StatusVerifikasi::Diajukan)->and($r->catatan_verifikasi)->toBeNull();
});

it('penolakan wajib menyertakan catatan', function () {
    $prodi = Prodi::factory()->create();
    $r = realisasiUntuk($prodi, 'diajukan');

    actingAs(userPeran(Peran::AdminFakultas));
    Livewire::test(ViewRealisasiKegiatan::class, ['record' => $r->getRouteKey()])
        ->callAction('tolak', ['catatan' => ''])
        ->assertHasActionErrors(['catatan' => 'required']);

    expect($r->fresh()->status_verifikasi)->toBe(StatusVerifikasi::Diajukan);
});

it('admin_prodi tidak boleh memverifikasi dan tidak boleh menyunting setelah diajukan', function () {
    $prodi = Prodi::factory()->create();
    $r = realisasiUntuk($prodi, 'diajukan');
    $user = pengusul($prodi);

    expect($user->can('verifikasi', $r))->toBeFalse()
        ->and($user->can('update', $r))->toBeFalse()
        ->and($user->can('ajukan', $r))->toBeFalse();

    actingAs($user);
    Livewire::test(ViewRealisasiKegiatan::class, ['record' => $r->getRouteKey()])
        ->assertActionHidden('verifikasi')
        ->assertActionHidden('tolak');
    Livewire::test(EditRealisasiKegiatan::class, ['record' => $r->getRouteKey()])->assertForbidden();
});

it('admin_prodi hanya boleh menyunting realisasi prodinya sendiri', function () {
    $prodiA = Prodi::factory()->create();
    $prodiB = Prodi::factory()->create();
    $milikB = realisasiUntuk($prodiB);
    $user = pengusul($prodiA);

    expect($user->can('update', $milikB))->toBeFalse()
        ->and($user->can('ajukan', $milikB))->toBeFalse()
        ->and($user->can('view', $milikB))->toBeTrue();

    actingAs($user);
    Livewire::test(EditRealisasiKegiatan::class, ['record' => $milikB->getRouteKey()])->assertForbidden();
});

it('pimpinan hanya melihat', function () {
    $prodi = Prodi::factory()->create();
    $r = realisasiUntuk($prodi, 'diajukan');
    $user = userPeran(Peran::Pimpinan);

    expect($user->can('view', $r))->toBeTrue()
        ->and($user->can('create', RealisasiKegiatan::class))->toBeFalse()
        ->and($user->can('update', $r))->toBeFalse()
        ->and($user->can('verifikasi', $r))->toBeFalse();

    actingAs($user);
    Livewire::test(ListRealisasiKegiatan::class)->assertSuccessful();
    Livewire::test(CreateRealisasiKegiatan::class)->assertForbidden();
});

it('admin_prodi membuat realisasi sebagai draf dengan prodinya otomatis ikut', function () {
    $prodi = Prodi::factory()->create();
    $ks = KerjaSama::factory()->create();
    actingAs(pengusul($prodi));

    Livewire::test(CreateRealisasiKegiatan::class)
        ->fillForm([
            'kerja_sama_id' => $ks->id,
            'judul_kegiatan' => 'PLP II',
            'dharma' => 'pendidikan',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-11-30',
            'manfaat_bagi_prodi' => 'Lahan praktik',
            'jumlah_mahasiswa' => 20,
            'jumlah_dosen' => 3,
            'prodi_ids' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $r = RealisasiKegiatan::firstWhere('judul_kegiatan', 'PLP II');

    expect($r->status_verifikasi)->toBe(StatusVerifikasi::Draf)
        ->and($r->prodi->pluck('id')->all())->toBe([$prodi->id])
        ->and($r->dibuat_oleh)->not->toBeNull();
});

it('dharma terisi otomatis dari bentuk kerja sama dan tanggal selesai tidak boleh sebelum mulai', function () {
    $this->seed(BentukKerjaSamaSeeder::class);
    $bentuk = BentukKerjaSama::firstWhere('kode', 'AK-PT-02'); // penelitian
    $ks = KerjaSama::factory()->create();
    actingAs(userPeran(Peran::AdminFakultas));

    Livewire::test(CreateRealisasiKegiatan::class)
        ->fillForm(['bentuk_kerja_sama_id' => $bentuk->id])
        ->assertFormSet(['dharma' => \App\Enums\Dharma::Penelitian])
        ->fillForm([
            'kerja_sama_id' => $ks->id, 'judul_kegiatan' => 'X', 'manfaat_bagi_prodi' => 'Y',
            'tanggal_mulai' => '2026-05-01', 'tanggal_selesai' => '2026-04-01',
        ])
        ->call('create')
        ->assertHasFormErrors(['tanggal_selesai']);
});

it('mengunggah bukti PDF lewat relation manager dan menolak jenis berkas yang salah', function () {
    Storage::fake('local');
    $prodi = Prodi::factory()->create();
    $r = realisasiUntuk($prodi);
    actingAs(pengusul($prodi));

    $komponen = fn () => Livewire::test(BerkasBuktiRelationManager::class, [
        'ownerRecord' => $r, 'pageClass' => EditRealisasiKegiatan::class,
    ]);

    $komponen()
        ->callAction(TestAction::make('create')->table(), [
            'jenis' => 'laporan',
            'path' => UploadedFile::fake()->createWithContent('laporan.pdf', '%PDF-1.4 '.str_repeat('x', 4096)),
        ])
        ->assertHasNoFormErrors();

    $bukti = BerkasBukti::first();

    expect($bukti)->not->toBeNull()
        ->and($bukti->realisasi_kegiatan_id)->toBe($r->id)
        ->and($bukti->nama_berkas)->toBe('laporan.pdf')
        ->and($bukti->ukuran_kb)->toBeGreaterThan(0);
    Storage::disk('local')->assertExists($bukti->path);

    $komponen()
        ->callAction(TestAction::make('create')->table(), [
            'jenis' => 'laporan',
            'path' => UploadedFile::fake()->image('foto.png'),
        ])
        ->assertHasFormErrors(['path']);

    expect(BerkasBukti::count())->toBe(1);
});

it('foto bukti hanya JPG/PNG dan maksimal 5 MB', function () {
    Storage::fake('local');
    $prodi = Prodi::factory()->create();
    $r = realisasiUntuk($prodi);
    actingAs(pengusul($prodi));

    $komponen = fn () => Livewire::test(BerkasBuktiRelationManager::class, [
        'ownerRecord' => $r, 'pageClass' => EditRealisasiKegiatan::class,
    ]);

    $komponen()
        ->callAction(TestAction::make('create')->table(), ['jenis' => 'foto', 'path' => UploadedFile::fake()->image('a.png')->size(6000)])
        ->assertHasFormErrors(['path']);

    $komponen()
        ->callAction(TestAction::make('create')->table(), ['jenis' => 'foto', 'path' => UploadedFile::fake()->image('a.png')->size(100)])
        ->assertHasNoFormErrors();

    expect(BerkasBukti::where('jenis', 'foto')->count())->toBe(1);
});

it('bukti tidak bisa ditambah atau dihapus setelah realisasi diajukan', function () {
    $prodi = Prodi::factory()->create();
    $r = realisasiUntuk($prodi, 'diajukan');
    $bukti = BerkasBukti::factory()->create(['realisasi_kegiatan_id' => $r->id]);
    $user = pengusul($prodi);

    expect($user->can('delete', $bukti))->toBeFalse()
        ->and($user->can('forceDelete', $bukti))->toBeFalse();

    actingAs($user);
    Livewire::test(BerkasBuktiRelationManager::class, ['ownerRecord' => $r, 'pageClass' => ViewRealisasiKegiatan::class])
        ->assertActionHidden(TestAction::make('create')->table())
        ->assertActionHidden(TestAction::make('delete')->table($bukti));
});

it('bukti PDF diunduh lewat route ber-Policy dan tidak tanpa login', function () {
    Storage::fake('local');
    Storage::disk('local')->put('bukti/laporan.pdf', '%PDF-1.4 contoh');
    $bukti = BerkasBukti::factory()->create();

    get(route('bukti.unduh', $bukti))->assertRedirect(route('filament.admin.auth.login'));

    actingAs(User::factory()->create());
    get(route('bukti.unduh', $bukti))->assertForbidden();

    actingAs(userPeran(Peran::Pimpinan));
    get(route('bukti.unduh', $bukti))->assertOk()->assertDownload('laporan.pdf');
});

it('halaman kerja sama menampilkan daftar realisasinya', function () {
    $prodi = Prodi::factory()->create();
    $r = realisasiUntuk($prodi);
    actingAs(userPeran(Peran::AdminFakultas));

    Livewire::test(RealisasiRelationManager::class, ['ownerRecord' => $r->kerjaSama, 'pageClass' => ViewKerjaSama::class])
        ->assertCanSeeTableRecords([$r]);
});

it('daftar realisasi dapat disaring menurut status, dharma, dan prodi', function () {
    $prodi = Prodi::factory()->create();
    $a = realisasiUntuk($prodi, 'diajukan');
    $b = RealisasiKegiatan::factory()->status('draf')->create(['dharma' => 'penelitian']);
    actingAs(userPeran(Peran::AdminFakultas));

    Livewire::test(ListRealisasiKegiatan::class)
        ->filterTable('status_verifikasi', 'diajukan')
        ->assertCanSeeTableRecords([$a])->assertCanNotSeeTableRecords([$b])
        ->resetTableFilters()
        ->filterTable('dharma', 'penelitian')
        ->assertCanSeeTableRecords([$b])->assertCanNotSeeTableRecords([$a])
        ->resetTableFilters()
        ->filterTable('prodi', $prodi->id)
        ->assertCanSeeTableRecords([$a])->assertCanNotSeeTableRecords([$b]);
});
