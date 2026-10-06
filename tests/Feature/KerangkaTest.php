<?php

use App\Enums\Peran;
use App\Models\User;
use Database\Seeders\RolSeeder;
use Database\Seeders\SuperAdminSeeder;
use Spatie\Permission\Models\Role;

beforeEach(fn () => $this->seed(RolSeeder::class));

it('membuat empat role', function () {
    expect(Role::pluck('name')->sort()->values()->all())
        ->toBe(['admin_fakultas', 'admin_prodi', 'pimpinan', 'super_admin']);
});

it('membuat satu super admin dan idempoten', function () {
    $this->seed(SuperAdminSeeder::class);
    $this->seed(SuperAdminSeeder::class);

    expect(User::role(Peran::SuperAdmin->value)->count())->toBe(1);
});

it('menampilkan halaman login panel admin', function () {
    $this->get('/admin/login')->assertOk();
});

it('mengalihkan tamu dari panel admin ke login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('mengizinkan user berperan masuk panel', function () {
    $user = User::factory()->create()->assignRole(Peran::AdminFakultas->value);

    $this->actingAs($user)->get('/admin')->assertOk();
});

it('menolak user tanpa peran masuk panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('memakai zona waktu dan locale Indonesia', function () {
    expect(config('app.timezone'))->toBe('Asia/Jakarta')
        ->and(config('app.locale'))->toBe('id')
        ->and(config('database.default'))->toBe('mariadb');
});

it('menampilkan pesan validasi dalam Bahasa Indonesia', function () {
    $pesan = validator(['nama' => ''], ['nama' => 'required'])->errors()->first('nama');

    expect($pesan)->toBe('nama wajib diisi.');
});
