<?php

namespace Database\Seeders;

use App\Enums\Peran;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'admin@kerjasama.test');
        $password = env('SUPER_ADMIN_PASSWORD');

        if (blank($password)) {
            abort_unless(app()->environment('local', 'testing'), 500, 'SUPER_ADMIN_PASSWORD wajib diisi.');
            $password = 'password';
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Super Admin', 'password' => $password],
        );

        $user->syncRoles([Peran::SuperAdmin->value]);
    }
}
