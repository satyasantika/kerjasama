<?php

namespace App\Policies;

use App\Enums\Peran;
use App\Models\KerjaSama;
use App\Models\User;

class KerjaSamaPolicy
{
    private function kelolaPenuh(User $user): bool
    {
        return $user->hasRole(Peran::AdminFakultas->value);
    }

    public function viewAny(User $user): bool
    {
        return $user->mengurusKerjaSama();
    }

    public function view(User $user, KerjaSama $kerjaSama): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->kelolaPenuh($user)
            || ($user->hasRole(Peran::AdminProdi->value) && $user->prodi_id !== null);
    }

    /** admin_prodi hanya boleh mengubah kerja sama yang melibatkan prodinya (pivot kerja_sama_prodi). */
    public function update(User $user, KerjaSama $kerjaSama): bool
    {
        if ($this->kelolaPenuh($user)) {
            return true;
        }

        return $user->hasRole(Peran::AdminProdi->value)
            && $kerjaSama->melibatkanProdi($user->prodi_id);
    }

    public function delete(User $user, KerjaSama $kerjaSama): bool
    {
        return $this->kelolaPenuh($user);
    }

    public function restore(User $user, KerjaSama $kerjaSama): bool
    {
        return $this->kelolaPenuh($user);
    }

    public function forceDelete(User $user, KerjaSama $kerjaSama): bool
    {
        return false;
    }
}
