<?php

use App\Enums\Peran;
use App\Models\User;
use Database\Seeders\RolSeeder;
use Filament\Facades\Filament;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as UserGoogle;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\withSession;

beforeEach(function () {
    $this->seed(RolSeeder::class);
    Filament::setCurrentPanel('admin');
    config([
        'services.google.aktif' => true,
        'services.google.client_id' => 'id-uji',
        'services.google.client_secret' => 'rahasia',
    ]);
});

function googleSebagai(string $email, array $tambahan = []): void
{
    Socialite::fake('google', UserGoogle::fake(array_merge([
        'id' => 'g-'.md5($email), 'name' => 'Nama Google', 'email' => $email, 'email_verified' => true,
    ], $tambahan)));
}

function urlMasuk(): string
{
    return Filament::getPanel('admin')->getLoginUrl();
}

function urlPanel(): string
{
    return Filament::getPanel('admin')->getUrl();
}

function penggunaBerperan(string $email = 'budi@unsil.ac.id', array $atribut = []): User
{
    $user = User::factory()->create(array_merge(['email' => $email], $atribut));
    $user->assignRole(Peran::AdminFakultas->value);

    return $user;
}

it('fitur nonaktif: kedua rute 404 dan halaman masuk tidak memuat tombol', function () {
    config(['services.google.aktif' => false]);

    get(route('auth.google.redirect'))->assertNotFound();
    get(route('auth.google.callback'))->assertNotFound();
    get(urlMasuk())->assertOk()->assertDontSee('Masuk dengan Google');
});

it('halaman masuk memuat tombol dan tautannya', function () {
    get(urlMasuk())
        ->assertOk()
        ->assertSee('Masuk dengan Google')
        ->assertSee(route('auth.google.redirect', ['asal' => urlMasuk()]), false);
});

it('mengarahkan ke Google dengan redirect_uri bawaan', function () {
    $lokasi = get(route('auth.google.redirect'))->assertRedirect()->headers->get('Location');

    expect($lokasi)->toStartWith('https://accounts.google.com/');
    parse_str(parse_url($lokasi, PHP_URL_QUERY), $q);
    expect($q['redirect_uri'])->toBe(route('auth.google.callback'));
    expect(urldecode($q['scope']))->toContain('openid');
});

it('memasukkan akun yang sudah ada dan menautkan google_id', function () {
    $user = penggunaBerperan();
    googleSebagai('budi@unsil.ac.id');

    get(route('auth.google.callback'))->assertRedirect(urlPanel());

    assertAuthenticatedAs($user);
    expect($user->refresh()->google_id)->toBe('g-'.md5('budi@unsil.ac.id'));
});

it('mencocokkan surel tanpa peduli huruf', function () {
    $user = penggunaBerperan();
    googleSebagai('Budi@Unsil.ac.id');

    get(route('auth.google.callback'));

    assertAuthenticatedAs($user);
    expect(User::count())->toBe(1);
});

it('membuat akun baru untuk surel yang belum terdaftar', function () {
    googleSebagai('baru@gmail.com');

    get(route('auth.google.callback'))
        ->assertSessionHas('galat_google', 'Akun Google Anda sudah tercatat, tetapi belum diberi peran. Hubungi admin.');

    $u = User::firstWhere('email', 'baru@gmail.com');
    expect($u)->not->toBeNull()
        ->and($u->name)->toBe('Nama Google')
        ->and($u->email_verified_at)->not->toBeNull()
        ->and($u->google_id)->toBe('g-'.md5('baru@gmail.com'));
    assertGuest();
});

it('menolak surel yang belum diverifikasi Google', function () {
    googleSebagai('x@gmail.com', ['email_verified' => false]);

    get(route('auth.google.callback'))
        ->assertSessionHas('galat_google', 'Surel akun Google Anda belum terverifikasi.');

    assertGuest();
    expect(User::count())->toBe(0);
});

it('menolak akun yang sudah tertaut ke Google lain', function () {
    penggunaBerperan(atribut: ['google_id' => 'google-lain']);
    googleSebagai('budi@unsil.ac.id');

    get(route('auth.google.callback'))
        ->assertSessionHas('galat_google', 'Akun ini sudah ditautkan ke akun Google lain.');

    assertGuest();
});

it('menolak akun nonaktif', function () {
    $user = penggunaBerperan();
    $user->forceFill(['aktif' => false])->save();
    googleSebagai('budi@unsil.ac.id');

    get(route('auth.google.callback'))
        ->assertSessionHas('galat_google', 'Akun Anda dinonaktifkan. Hubungi admin.');

    assertGuest();
});

it('membatalkan di Google kembali ke halaman masuk', function () {
    get(route('auth.google.callback', ['error' => 'access_denied']))
        ->assertRedirect(urlMasuk())
        ->assertSessionHas('galat_google', 'Masuk dengan Google dibatalkan.');

    assertGuest();
});

it('state OAuth tidak cocok kembali ke halaman masuk', function () {
    Socialite::fake('google', fn () => throw new InvalidStateException);

    get(route('auth.google.callback'))
        ->assertRedirect(urlMasuk())
        ->assertSessionHas('galat_google', 'Sesi masuk Google kedaluwarsa. Silakan coba lagi.');

    assertGuest();
});

it('kembali ke asal bila asal berada di dalam aplikasi, mengabaikan asal di luar aplikasi', function () {
    withSession(['google_asal' => url('/halaman-x')])
        ->get(route('auth.google.callback', ['error' => 'access_denied']))
        ->assertRedirect(url('/halaman-x'));

    withSession(['google_asal' => 'https://evil.example/'])
        ->get(route('auth.google.callback', ['error' => 'access_denied']))
        ->assertRedirect(urlMasuk());

    withSession(['google_asal' => rtrim(url('/'), '/').'.evil.example/'])
        ->get(route('auth.google.callback', ['error' => 'access_denied']))
        ->assertRedirect(route('filament.admin.auth.login'));
});

it('tetap mengarahkan ke Google bila asal bukan string', function () {
    get(route('auth.google.redirect', ['asal' => ['x']]))
        ->assertRedirect()
        ->assertSessionMissing('galat_google');
});

it('pengguna yang sudah masuk langsung diarahkan', function () {
    actingAs(penggunaBerperan())
        ->get(route('auth.google.redirect'))
        ->assertRedirect(urlPanel());
});

it('membatasi 10 permintaan per menit per IP', function () {
    googleSebagai('x@gmail.com', ['email_verified' => false]);

    foreach (range(1, 10) as $i) {
        get(route('auth.google.callback'));
    }

    get(route('auth.google.callback'))->assertStatus(429);
});
