<?php

use App\Enums\Peran;
use App\Filament\Auth\EditProfil;
use App\Models\User;
use Database\Seeders\RolSeeder;
use Filament\Auth\Pages\EditProfile;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed(RolSeeder::class);
    Filament::setCurrentPanel('admin');
});

it('halaman login menampilkan sakelar tema dan tautan beranda', function () {
    get('/admin/login')
        ->assertOk()
        ->assertSee('data-sakelar-tema', false)
        ->assertSee('Kembali ke beranda');
});

it('landing menyediakan sakelar tema terang/gelap', function () {
    get('/')->assertOk()->assertSee('data-sakelar-tema', false);
});

it('halaman 404 representatif dengan tombol kembali dan beranda', function () {
    get('/halaman-yang-tidak-ada')
        ->assertNotFound()
        ->assertSee('Halaman tidak ditemukan')
        ->assertSee('data-kembali', false)
        ->assertSee('Beranda');
});

it('tamu diarahkan ke login saat membuka profil', function () {
    get('/admin/profile')->assertRedirect();
});

it('pengguna dapat mengubah nama dan kata sandi lewat halaman profil', function () {
    $user = User::factory()->create(['password' => 'lama-12345'])->assignRole(Peran::AdminProdi->value);
    actingAs($user);

    Livewire::test(EditProfile::class)
        ->fillForm([
            'name' => 'Nama Baru',
            'email' => $user->email,
            'password' => 'baru-67890-Aa',
            'passwordConfirmation' => 'baru-67890-Aa',
            'currentPassword' => 'lama-12345',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();
    expect($user->name)->toBe('Nama Baru')
        ->and(Hash::check('baru-67890-Aa', $user->password))->toBeTrue();
});

it('ganti kata sandi ditolak bila kata sandi saat ini salah', function () {
    $user = User::factory()->create(['password' => 'lama-12345'])->assignRole(Peran::AdminProdi->value);
    actingAs($user);

    Livewire::test(EditProfile::class)
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'baru-67890-Aa',
            'passwordConfirmation' => 'baru-67890-Aa',
            'currentPassword' => 'salah-banget',
        ])
        ->call('save')
        ->assertHasFormErrors(['currentPassword']);

    expect(Hash::check('lama-12345', $user->fresh()->password))->toBeTrue();
});

it('landing menautkan panduan langsung ke index.html agar tidak memicu redirect tanpa subpath', function () {
    get('/')->assertOk()
        ->assertSee(url('/panduan/index.html'), false)
        ->assertDontSee(url('/panduan').'"', false);
});

it('setiap halaman panduan statis memiliki sakelar tema', function () {
    foreach (glob(public_path('panduan/*.html')) as $berkas) {
        expect(file_get_contents($berkas))->toContain('data-sakelar-tema');
    }
});

it('pengguna dengan wajib_ganti_sandi diarahkan ke profil', function () {
    $user = User::factory()->create(['wajib_ganti_sandi' => true])->assignRole(Peran::AdminProdi->value);

    actingAs($user)->get('/admin')->assertRedirect(Filament::getProfileUrl());
});

it('mengganti sandi di profil mencabut kewajiban dan mengeluarkan perangkat lain', function () {
    $user = User::factory()->create(['password' => 'lama-12345', 'wajib_ganti_sandi' => true])->assignRole(Peran::AdminProdi->value);
    Event::fake([OtherDeviceLogout::class]);
    actingAs($user);

    Livewire::test(EditProfil::class)
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'baru-67890-Aa',
            'passwordConfirmation' => 'baru-67890-Aa',
            'currentPassword' => 'lama-12345',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();
    expect($user->wajib_ganti_sandi)->toBeFalse()
        ->and(Hash::check('baru-67890-Aa', $user->password))->toBeTrue();
    Event::assertDispatched(OtherDeviceLogout::class);

    get('/admin')->assertOk();
});
