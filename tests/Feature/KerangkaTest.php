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

it('semua tabel domain memakai UUID versi 7 sebagai primary key', function () {
    $ks = \App\Models\KerjaSama::factory()->create();
    $baris = [
        User::factory()->create(),
        \App\Models\Prodi::factory()->create(),
        \App\Models\ProdiNdtps::create(['prodi_id' => \App\Models\Prodi::factory()->create()->id, 'tahun_ts' => 2025, 'ndtps' => 5]),
        \App\Models\Mitra::factory()->create(),
        \App\Models\BentukKerjaSama::create(['kode' => 'X-1', 'nama' => 'X', 'bidang' => 'akademik', 'ruang' => 'antar_pt', 'pasal_rujukan' => '-']),
        $ks,
        \App\Models\RealisasiKegiatan::factory()->create(['kerja_sama_id' => $ks->id]),
        \App\Models\BerkasBukti::factory()->create(),
        \App\Models\EvaluasiKerjaSama::factory()->create(),
        \App\Models\PengingatTerkirim::create(['kerja_sama_id' => $ks->id, 'ambang_hari' => 30, 'dikirim_pada' => now()]),
    ];

    foreach ($baris as $model) {
        expect(\Illuminate\Support\Str::isUuid($model->getKey()))->toBeTrue(class_basename($model).' bukan UUID')
            ->and($model->getKey()[14])->toBe('7', class_basename($model).' bukan UUID versi 7');
    }

    // UUIDv7 berurutan waktu: yang dibuat belakangan tidak lebih kecil
    $a = \App\Models\Mitra::factory()->create();
    usleep(2000);
    $b = \App\Models\Mitra::factory()->create();

    expect($b->id > $a->id)->toBeTrue();
});
