<?php

use App\Enums\Peran;
use App\Filament\Resources\KerjaSama\Pages\CreateKerjaSama;
use App\Filament\Resources\KerjaSama\Pages\ViewKerjaSama;
use App\Filament\Resources\RealisasiKegiatan\Pages\EditRealisasiKegiatan;
use App\Filament\Resources\RealisasiKegiatan\RelationManagers\BerkasBuktiRelationManager;
use App\Models\BerkasBukti;
use App\Models\KerjaSama;
use App\Models\Mitra;
use App\Models\Prodi;
use App\Models\RealisasiKegiatan;
use App\Models\User;
use App\Rules\TautanBerkasValid;
use Database\Seeders\RolSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed(RolSeeder::class);
    Filament::setCurrentPanel('admin');
});

function formKs(array $ubah = []): array
{
    return [
        'jenis_dokumen' => 'MoA', 'judul' => 'Dokumen Tautan', 'ruang_lingkup' => 'X', 'bidang' => 'akademik',
        'tingkat' => 'lokal', 'pihak_penandatangan_unsil' => 'dekan',
        'tanggal_tanda_tangan' => '2026-01-10', 'tanggal_mulai' => '2026-01-10', 'tanggal_berakhir' => '2029-01-09',
        ...$ubah,
    ];
}

it('menerima hanya tautan https Google Drive/Docs', function (string $url, bool $sah) {
    expect(TautanBerkasValid::sah($url))->toBe($sah);
})->with([
    ['https://drive.google.com/file/d/abc123/view?usp=sharing', true],
    ['https://docs.google.com/document/d/abc/edit', true],
    ['http://drive.google.com/file/d/abc/view', false],
    ['https://bit.ly/abc', false],
    ['https://drive.google.com.evil.test/file/d/abc', false],
    ['https://evil.test/https://drive.google.com/x', false],
    ['https://user:pw@drive.google.com/file/d/abc', false],
    ['javascript:alert(1)', false],
    ['', false],
]);

it('upload dimatikan secara bawaan dan tautan tersedia di form kerja sama', function () {
    $mitra = Mitra::factory()->create();
    actingAs(User::factory()->create()->assignRole(Peran::AdminFakultas->value));

    expect(config('berkas.unggah_aktif'))->toBeFalse();

    Livewire::test(CreateKerjaSama::class)
        ->assertFormFieldHidden('berkas_dokumen')
        ->assertFormFieldVisible('tautan_dokumen')
        ->fillForm(formKs(['mitra' => [$mitra->id], 'tautan_dokumen' => 'https://drive.google.com/file/d/abc123/view']))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(KerjaSama::firstWhere('judul', 'Dokumen Tautan')->tautan_dokumen)->toBe('https://drive.google.com/file/d/abc123/view');
});

it('form kerja sama menolak tautan di luar Google Drive', function () {
    $mitra = Mitra::factory()->create();
    actingAs(User::factory()->create()->assignRole(Peran::AdminFakultas->value));

    Livewire::test(CreateKerjaSama::class)
        ->fillForm(formKs(['mitra' => [$mitra->id], 'tautan_dokumen' => 'https://bit.ly/naskah']))
        ->call('create')
        ->assertHasFormErrors(['tautan_dokumen']);
});

it('unggah muncul kembali bila BERKAS_UNGGAH diaktifkan', function () {
    config(['berkas.unggah_aktif' => true]);
    actingAs(User::factory()->create()->assignRole(Peran::AdminFakultas->value));

    Livewire::test(CreateKerjaSama::class)->assertFormFieldVisible('berkas_dokumen');
});

it('tautan dokumen dibuka lewat route ber-Policy dan hanya ke host daftar putih', function () {
    $ks = KerjaSama::factory()->create(['tautan_dokumen' => 'https://drive.google.com/file/d/abc/view']);

    get(route('tautan.kerja-sama', [$ks, 'dokumen']))->assertRedirect(route('filament.admin.auth.login'));

    actingAs(User::factory()->create());
    get(route('tautan.kerja-sama', [$ks, 'dokumen']))->assertForbidden();

    actingAs(User::factory()->create()->assignRole(Peran::Pimpinan->value));
    get(route('tautan.kerja-sama', [$ks, 'dokumen']))->assertRedirect('https://drive.google.com/file/d/abc/view');
    get(route('tautan.kerja-sama', [$ks, 'asing']))->assertNotFound();

    $ks->forceFill(['tautan_dokumen' => 'https://evil.test/phish'])->save();
    get(route('tautan.kerja-sama', [$ks, 'dokumen']))->assertNotFound();
});

it('halaman detail menampilkan tombol buka tautan tanpa membuka URL mentah di HTML', function () {
    $ks = KerjaSama::factory()->create(['tautan_dokumen' => 'https://drive.google.com/file/d/rahasia/view']);
    actingAs(User::factory()->create()->assignRole(Peran::Pimpinan->value));

    Livewire::test(ViewKerjaSama::class, ['record' => $ks->getRouteKey()])
        ->assertSee('Buka dokumen perjanjian (Google Drive)')
        ->assertDontSee('file/d/rahasia');
});

it('bukti realisasi ditambah lewat tautan dan dapat dibuka lewat route ber-Policy', function () {
    $prodi = Prodi::factory()->create();
    $ks = KerjaSama::factory()->create();
    $ks->prodi()->attach($prodi->id, ['penginisiasi' => true]);
    $r = RealisasiKegiatan::factory()->create(['kerja_sama_id' => $ks->id]);
    $r->prodi()->sync([$prodi->id]);
    $pengusul = User::factory()->create(['prodi_id' => $prodi->id])->assignRole(Peran::AdminProdi->value);
    actingAs($pengusul);

    Livewire::test(BerkasBuktiRelationManager::class, ['ownerRecord' => $r, 'pageClass' => EditRealisasiKegiatan::class])
        ->callAction(TestAction::make('create')->table(), [
            'jenis' => 'laporan', 'nama_berkas' => 'Laporan kegiatan', 'tautan' => 'https://drive.google.com/file/d/lap/view',
        ])
        ->assertHasNoFormErrors();

    $bukti = BerkasBukti::firstWhere('nama_berkas', 'Laporan kegiatan');
    expect($bukti->path)->toBeNull()
        ->and($r->fresh()->punyaLaporan())->toBeTrue();

    get(route('tautan.bukti', $bukti))->assertRedirect('https://drive.google.com/file/d/lap/view');

    Livewire::test(BerkasBuktiRelationManager::class, ['ownerRecord' => $r, 'pageClass' => EditRealisasiKegiatan::class])
        ->callAction(TestAction::make('create')->table(), ['jenis' => 'foto', 'nama_berkas' => 'Foto', 'tautan' => 'https://bit.ly/foto'])
        ->assertHasFormErrors(['tautan']);
});
