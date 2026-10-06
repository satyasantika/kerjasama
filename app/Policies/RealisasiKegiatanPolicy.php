<?php

namespace App\Policies;

use App\Enums\Peran;
use App\Enums\StatusVerifikasi;
use App\Models\RealisasiKegiatan;
use App\Models\User;

class RealisasiKegiatanPolicy
{
    private function kelolaPenuh(User $user): bool
    {
        return $user->hasAnyRole([Peran::SuperAdmin->value, Peran::AdminFakultas->value]);
    }

    private function pengusul(User $user, RealisasiKegiatan $realisasi): bool
    {
        return $user->hasRole(Peran::AdminProdi->value)
            && $realisasi->melibatkanProdi($user->prodi_id);
    }

    public function viewAny(User $user): bool
    {
        return $user->roles()->exists();
    }

    public function view(User $user, RealisasiKegiatan $realisasi): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->kelolaPenuh($user)
            || ($user->hasRole(Peran::AdminProdi->value) && $user->prodi_id !== null);
    }

    /** admin_prodi hanya boleh menyunting realisasi prodinya selagi draf/ditolak. */
    public function update(User $user, RealisasiKegiatan $realisasi): bool
    {
        if ($this->kelolaPenuh($user)) {
            return true;
        }

        return $this->pengusul($user, $realisasi) && $realisasi->bisaDisunting();
    }

    public function ajukan(User $user, RealisasiKegiatan $realisasi): bool
    {
        return $realisasi->bisaDisunting()
            && ($this->kelolaPenuh($user) || $this->pengusul($user, $realisasi));
    }

    /** Verifikasi/penolakan hanya oleh admin_fakultas (dan super_admin) atas realisasi yang diajukan. */
    public function verifikasi(User $user, RealisasiKegiatan $realisasi): bool
    {
        return $this->kelolaPenuh($user)
            && $realisasi->status_verifikasi === StatusVerifikasi::Diajukan;
    }

    public function delete(User $user, RealisasiKegiatan $realisasi): bool
    {
        return $this->kelolaPenuh($user);
    }

    public function restore(User $user, RealisasiKegiatan $realisasi): bool
    {
        return $this->kelolaPenuh($user);
    }

    public function forceDelete(User $user, RealisasiKegiatan $realisasi): bool
    {
        return false;
    }
}
