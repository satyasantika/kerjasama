<?php

use App\Actions\Pengguna\SimpanPengguna;
use App\Enums\Peran;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\KerjaSama\Pages\ListKerjaSama;
use App\Filament\Resources\Pengguna\Pages\CreatePengguna;
use App\Filament\Resources\Pengguna\Pages\EditPengguna;
use App\Filament\Resources\Pengguna\Pages\ListPengguna;
use App\Models\KerjaSama;
use App\Models\Prodi;
use App\Models\User;
use Database\Seeders\RolSeeder;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed(RolSeeder::class);
    Filament::setCurrentPanel('admin');
});

function penggunaDengan(Peran $peran, array $atribut = []): User
{
    return User::factory()->create($atribut)->assignRole($peran->value);
}

it('super_admin tidak mengurus data kerja sama', function () {
    $superAdmin = penggunaDengan(Peran::SuperAdmin);
    $ks = KerjaSama::factory()->create();

    expect($superAdmin->can('viewAny', KerjaSama::class))->toBeFalse()
        ->and($superAdmin->can('create', KerjaSama::class))->toBeFalse()
        ->and($superAdmin->can('update', $ks))->toBeFalse()
        ->and($superAdmin->can('delete', $ks))->toBeFalse()
        ->and($superAdmin->can('create', Prodi::class))->toBeFalse();

    actingAs($superAdmin);
    Livewire::test(ListKerjaSama::class)->assertForbidden();
    get(route('laporan.rekap-pimpinan'))->assertForbidden();
});

it('dasbor super_admin tidak memuat widget dan unduhan kerja sama', function () {
    actingAs(penggunaDengan(Peran::SuperAdmin));

    Livewire::test(Dashboard::class)
        ->assertSuccessful()
        ->assertDontSee('Unduh rekap PDF');
});

it('hanya super_admin yang dapat membuka daftar pengguna', function (Peran $peran, bool $boleh) {
    actingAs(penggunaDengan($peran));

    $uji = Livewire::test(ListPengguna::class);
    $boleh ? $uji->assertSuccessful() : $uji->assertForbidden();
})->with([
    [Peran::SuperAdmin, true],
    [Peran::AdminFakultas, false],
    [Peran::AdminProdi, false],
    [Peran::Pimpinan, false],
]);

it('super_admin menambah admin fakultas lewat menu', function () {
    actingAs(penggunaDengan(Peran::SuperAdmin));

    Livewire::test(CreatePengguna::class)
        ->fillForm([
            'name' => 'Admin Fakultas Baru',
            'email' => 'af@unsil.test',
            'password' => 'rahasia-123',
            'peran' => Peran::AdminFakultas->value,
            'aktif' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $baru = User::where('email', 'af@unsil.test')->firstOrFail();
    expect($baru->hasRole(Peran::AdminFakultas->value))->toBeTrue()
        ->and($baru->prodi_id)->toBeNull()
        ->and($baru->aktif)->toBeTrue()
        ->and(Hash::check('rahasia-123', $baru->password))->toBeTrue();
});

it('super_admin menambah admin prodi dan prodi wajib diisi', function () {
    actingAs(penggunaDengan(Peran::SuperAdmin));
    $prodi = Prodi::factory()->create();

    Livewire::test(CreatePengguna::class)
        ->fillForm(['name' => 'AP', 'email' => 'ap@unsil.test', 'password' => 'rahasia-123', 'peran' => Peran::AdminProdi->value])
        ->call('create')
        ->assertHasFormErrors(['prodi_id' => 'required']);

    Livewire::test(CreatePengguna::class)
        ->fillForm(['name' => 'AP', 'email' => 'ap@unsil.test', 'password' => 'rahasia-123', 'peran' => Peran::AdminProdi->value, 'prodi_id' => $prodi->id])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(User::where('email', 'ap@unsil.test')->first()->prodi_id)->toBe($prodi->id);
});

it('email harus unik dan kata sandi minimal 8 karakter', function () {
    actingAs(penggunaDengan(Peran::SuperAdmin));
    penggunaDengan(Peran::Pimpinan, ['email' => 'dipakai@unsil.test']);

    Livewire::test(CreatePengguna::class)
        ->fillForm(['name' => 'X', 'email' => 'dipakai@unsil.test', 'password' => 'pendek', 'peran' => Peran::Pimpinan->value])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique', 'password' => 'min']);
});

it('mengubah peran dan menonaktifkan pengguna; kata sandi kosong tidak mengubah sandi lama', function () {
    actingAs(penggunaDengan(Peran::SuperAdmin));
    $target = penggunaDengan(Peran::Pimpinan, ['password' => 'sandi-lama-1']);

    Livewire::test(EditPengguna::class, ['record' => $target->getRouteKey()])
        ->fillForm(['peran' => Peran::AdminFakultas->value, 'aktif' => false, 'password' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    $target->refresh();
    expect($target->hasRole(Peran::AdminFakultas->value))->toBeTrue()
        ->and($target->hasRole(Peran::Pimpinan->value))->toBeFalse()
        ->and($target->aktif)->toBeFalse()
        ->and(Hash::check('sandi-lama-1', $target->password))->toBeTrue();
});

it('akun nonaktif tidak dapat mengakses panel', function () {
    $aktif = penggunaDengan(Peran::AdminFakultas);
    $nonaktif = penggunaDengan(Peran::AdminFakultas, ['aktif' => false]);

    expect($aktif->canAccessPanel(Filament::getCurrentPanel()))->toBeTrue()
        ->and($nonaktif->canAccessPanel(Filament::getCurrentPanel()))->toBeFalse();
});

it('super_admin tidak dapat menurunkan atau menonaktifkan dirinya sendiri', function () {
    $diri = penggunaDengan(Peran::SuperAdmin);
    $lain = penggunaDengan(Peran::SuperAdmin);
    $data = ['name' => $diri->name, 'email' => $diri->email, 'peran' => Peran::AdminFakultas->value, 'aktif' => true];

    expect(fn () => app(SimpanPengguna::class)->handle($diri, $data, $diri))->toThrow(ValidationException::class)
        ->and(fn () => app(SimpanPengguna::class)->handle($diri, [...$data, 'peran' => Peran::SuperAdmin->value, 'aktif' => false], $diri))->toThrow(ValidationException::class);

    expect($diri->fresh()->hasRole(Peran::SuperAdmin->value))->toBeTrue();
    expect($lain)->not->toBeNull();
});

it('harus tersisa minimal satu super_admin aktif', function () {
    $pertama = penggunaDengan(Peran::SuperAdmin);
    $kedua = penggunaDengan(Peran::SuperAdmin);
    $ubah = fn (User $u, array $x) => app(SimpanPengguna::class)->handle($u, ['name' => $u->name, 'email' => $u->email, 'peran' => Peran::SuperAdmin->value, 'aktif' => true, ...$x], $pertama);

    $ubah($kedua, ['aktif' => false]); // masih ada $pertama aktif: boleh
    expect($kedua->fresh()->aktif)->toBeFalse();

    $pelaku = penggunaDengan(Peran::SuperAdmin, ['aktif' => false]);
    $pertama->update(['aktif' => true]);
    expect(fn () => app(SimpanPengguna::class)->handle($pertama, ['name' => 'x', 'email' => $pertama->email, 'peran' => Peran::Pimpinan->value, 'aktif' => true], $pelaku))
        ->toThrow(ValidationException::class);
});

it('pengguna tidak pernah dapat dihapus lewat policy', function () {
    $superAdmin = penggunaDengan(Peran::SuperAdmin);

    expect($superAdmin->can('delete', penggunaDengan(Peran::Pimpinan)))->toBeFalse();
});

it('super_admin dapat menyamar sebagai peran kerja sama lalu kembali', function () {
    $superAdmin = penggunaDengan(Peran::SuperAdmin);
    $target = penggunaDengan(Peran::AdminProdi, ['prodi_id' => Prodi::factory()->create()->id]);
    actingAs($superAdmin);

    Livewire::test(ListPengguna::class)
        ->callTableAction('menyamar', $target)
        ->assertRedirect();

    expect(auth()->id())->toBe($target->id)
        ->and(session('penyamar_id'))->toBe($superAdmin->id);

    get('/admin')->assertOk()->assertSee('Anda sedang menyamar sebagai')->assertSee('Kembali ke akun saya');

    $this->post(route('menyamar.akhiri'))->assertRedirect();

    expect(auth()->id())->toBe($superAdmin->id)
        ->and(session()->has('penyamar_id'))->toBeFalse();
});

it('penyamaran ditolak untuk diri sendiri, super_admin lain, akun nonaktif, dan bertingkat', function () {
    $superAdmin = penggunaDengan(Peran::SuperAdmin);
    $lain = penggunaDengan(Peran::SuperAdmin);
    $nonaktif = penggunaDengan(Peran::Pimpinan, ['aktif' => false]);
    $biasa = penggunaDengan(Peran::Pimpinan);

    expect($superAdmin->can('impersonate', $superAdmin))->toBeFalse()
        ->and($superAdmin->can('impersonate', $lain))->toBeFalse()
        ->and($superAdmin->can('impersonate', $nonaktif))->toBeFalse()
        ->and($superAdmin->can('impersonate', $biasa))->toBeTrue();

    session(['penyamar_id' => $superAdmin->id]);
    expect($superAdmin->can('impersonate', $biasa))->toBeFalse();
});

it('peran selain super_admin tidak dapat menyamar', function (Peran $peran) {
    $pelaku = penggunaDengan($peran);

    expect($pelaku->can('impersonate', penggunaDengan(Peran::Pimpinan)))->toBeFalse();
})->with([Peran::AdminFakultas, Peran::AdminProdi, Peran::Pimpinan]);

it('mengakhiri penyamaran tanpa sesi penyamaran ditolak', function () {
    actingAs(penggunaDengan(Peran::AdminFakultas));

    $this->post(route('menyamar.akhiri'))->assertForbidden();
});

it('kembali dari penyamaran keluar bila super_admin asal sudah nonaktif', function () {
    $asal = penggunaDengan(Peran::SuperAdmin, ['aktif' => false]);
    actingAs(penggunaDengan(Peran::Pimpinan));

    $this->withSession(['penyamar_id' => $asal->id])->post(route('menyamar.akhiri'))->assertRedirect();

    expect(auth()->check())->toBeFalse();
});

function berkasImpor(string $isi, string $nama = 'pengguna.csv'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($nama, $isi);
}

it('impor massal membuat pengguna, mengaitkan prodi, dan membangkitkan kata sandi kosong', function () {
    $prodi = Prodi::factory()->create(['kode' => 'PMAT']);
    actingAs(penggunaDengan(Peran::SuperAdmin));

    $csv = "nama,email,peran,kode_prodi,kata_sandi\n"
        ."CONTOH,contoh@x.test,admin_prodi,PMAT,\n"
        ."Ani,ani@unsil.test,admin_prodi,pmat,\n"
        ."Budi,budi@unsil.test,Admin Fakultas,,sandi-budi-1\n"
        ."Citra,citra@unsil.test,pimpinan,,\n";

    Livewire::test(ListPengguna::class)
        ->callAction('imporPengguna', ['berkas' => berkasImpor($csv)])
        ->assertHasNoActionErrors()
        ->assertFileDownloaded();

    $ani = User::firstWhere('email', 'ani@unsil.test');
    $budi = User::firstWhere('email', 'budi@unsil.test');

    expect(User::whereIn('email', ['ani@unsil.test', 'budi@unsil.test', 'citra@unsil.test'])->count())->toBe(3)
        ->and(User::where('email', 'contoh@x.test')->exists())->toBeFalse()
        ->and($ani->hasRole(Peran::AdminProdi->value))->toBeTrue()
        ->and($ani->prodi_id)->toBe($prodi->id)
        ->and($ani->aktif)->toBeTrue()
        ->and($budi->hasRole(Peran::AdminFakultas->value))->toBeTrue()
        ->and(Hash::check('sandi-budi-1', $budi->password))->toBeTrue();
});

it('impor massal membatalkan seluruh berkas bila ada baris tidak sah', function () {
    Prodi::factory()->create(['kode' => 'PMAT']);
    penggunaDengan(Peran::Pimpinan, ['email' => 'ada@unsil.test']);
    actingAs(penggunaDengan(Peran::SuperAdmin));
    $sebelum = User::count();

    $csv = "nama,email,peran,kode_prodi,kata_sandi\n"
        ."Baik,baik@unsil.test,pimpinan,,\n"
        ."Dobel,ada@unsil.test,pimpinan,,\n"
        ."TanpaProdi,tp@unsil.test,admin_prodi,,\n"
        ."SuperBaru,sb@unsil.test,super_admin,,\n"
        ."Pendek,pd@unsil.test,pimpinan,,abc\n"
        ."Kembar,baik@unsil.test,pimpinan,,\n";

    Livewire::test(ListPengguna::class)
        ->callAction('imporPengguna', ['berkas' => berkasImpor($csv)])
        ->assertNotified('Impor dibatalkan: tidak ada akun yang dibuat');

    expect(User::count())->toBe($sebelum);
});

it('impor massal menolak berkas tanpa kolom wajib dan peran selain super_admin tidak melihat aksinya', function () {
    actingAs(penggunaDengan(Peran::SuperAdmin));

    Livewire::test(ListPengguna::class)
        ->callAction('imporPengguna', ['berkas' => berkasImpor("a,b\n1,2\n")])
        ->assertNotified('Impor gagal');

    actingAs(penggunaDengan(Peran::AdminFakultas));
    Livewire::test(ListPengguna::class)->assertForbidden();
});

it('impor massal membaca CSV berpemisah titik koma dan XLSX', function () {
    actingAs(penggunaDengan(Peran::SuperAdmin));

    Livewire::test(ListPengguna::class)
        ->callAction('imporPengguna', ['berkas' => berkasImpor("nama;email;peran;kode_prodi;kata_sandi\nDedi;dedi@unsil.test;pimpinan;;\n")])
        ->assertFileDownloaded();

    $path = tempnam(sys_get_temp_dir(), 'x').'.xlsx';
    $w = new Writer;
    $w->openToFile($path);
    $w->addRow(Row::fromValues(['nama', 'email', 'peran', 'kode_prodi', 'kata_sandi']));
    $w->addRow(Row::fromValues(['Eka', 'eka@unsil.test', 'admin_fakultas', '', '']));
    $w->close();

    Livewire::test(ListPengguna::class)
        ->callAction('imporPengguna', ['berkas' => berkasImpor(file_get_contents($path), 'pengguna.xlsx')])
        ->assertFileDownloaded();

    expect(User::whereIn('email', ['dedi@unsil.test', 'eka@unsil.test'])->count())->toBe(2);
});
