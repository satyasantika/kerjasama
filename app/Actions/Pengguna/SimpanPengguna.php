<?php

namespace App\Actions\Pengguna;

use App\Enums\Peran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SimpanPengguna
{
    /**
     * Buat atau ubah pengguna beserta perannya.
     *
     * @param  array{name: string, email: string, password?: ?string, peran: string|Peran, prodi_id?: ?string, aktif?: bool}  $data
     */
    public function handle(?User $pengguna, array $data, User $pelaku): User
    {
        $peran = $data['peran'] instanceof Peran ? $data['peran'] : Peran::from($data['peran']);
        $aktif = (bool) ($data['aktif'] ?? true);
        $prodiId = match ($peran) {
            Peran::AdminProdi, Peran::Pimpinan => $data['prodi_id'] ?? null,
            default => null,
        };

        if ($peran === Peran::AdminProdi && blank($prodiId)) {
            throw ValidationException::withMessages(['data.prodi_id' => 'Admin Prodi wajib dikaitkan dengan program studi.']);
        }

        if ($pengguna?->exists) {
            $this->jagaKunciDiri($pengguna, $pelaku, $peran, $aktif);
            $this->jagaSuperAdminTerakhir($pengguna, $peran, $aktif);
        }

        return DB::transaction(function () use ($pengguna, $data, $peran, $prodiId, $aktif) {
            $pengguna ??= new User;
            $pengguna->fill(['name' => $data['name'], 'email' => $data['email']]);

            if (filled($data['password'] ?? null)) {
                $pengguna->password = $data['password'];
            }

            $pengguna->forceFill(['prodi_id' => $prodiId, 'aktif' => $aktif])->save();
            $pengguna->syncRoles([$peran->value]);

            return $pengguna;
        });
    }

    private function jagaKunciDiri(User $pengguna, User $pelaku, Peran $peran, bool $aktif): void
    {
        if ($pengguna->is($pelaku) && ($peran !== Peran::SuperAdmin || ! $aktif)) {
            throw ValidationException::withMessages([
                'data.peran' => 'Anda tidak dapat menurunkan peran atau menonaktifkan akun Anda sendiri.',
            ]);
        }
    }

    private function jagaSuperAdminTerakhir(User $pengguna, Peran $peran, bool $aktif): void
    {
        $sedangSuperAdminAktif = $pengguna->aktif && $pengguna->hasRole(Peran::SuperAdmin->value);
        $tetapSuperAdminAktif = $peran === Peran::SuperAdmin && $aktif;

        if (! $sedangSuperAdminAktif || $tetapSuperAdminAktif) {
            return;
        }

        $lain = User::role(Peran::SuperAdmin->value)->where('aktif', true)->whereKeyNot($pengguna->getKey())->exists();

        if (! $lain) {
            throw ValidationException::withMessages([
                'data.peran' => 'Harus tersisa minimal satu Super Admin aktif.',
            ]);
        }
    }
}
