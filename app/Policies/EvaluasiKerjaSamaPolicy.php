<?php

namespace App\Policies;

use App\Enums\Peran;
use App\Models\EvaluasiKerjaSama;
use App\Models\User;

/** Evaluasi mengikuti hak ubah kerja sama induknya (admin_prodi hanya yang melibatkan prodinya). */
class EvaluasiKerjaSamaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->mengurusKerjaSama();
    }

    public function view(User $user, EvaluasiKerjaSama $evaluasi): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Peran::AdminFakultas->value)
            || ($user->hasRole(Peran::AdminProdi->value) && $user->prodi_id !== null);
    }

    public function update(User $user, EvaluasiKerjaSama $evaluasi): bool
    {
        return $user->can('update', $evaluasi->kerjaSama);
    }

    public function delete(User $user, EvaluasiKerjaSama $evaluasi): bool
    {
        return false;
    }
}
