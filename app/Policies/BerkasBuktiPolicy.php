<?php

namespace App\Policies;

use App\Models\BerkasBukti;
use App\Models\RealisasiKegiatan;
use App\Models\User;

/** Bukti mengikuti hak sunting realisasi induknya. */
class BerkasBuktiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->mengurusKerjaSama();
    }

    public function view(User $user, BerkasBukti $berkas): bool
    {
        return $user->can('view', $berkas->realisasiKegiatan);
    }

    public function create(User $user): bool
    {
        return $user->can('create', RealisasiKegiatan::class);
    }

    public function update(User $user, BerkasBukti $berkas): bool
    {
        return $user->can('update', $berkas->realisasiKegiatan);
    }

    public function delete(User $user, BerkasBukti $berkas): bool
    {
        return $user->can('update', $berkas->realisasiKegiatan);
    }

    public function restore(User $user, BerkasBukti $berkas): bool
    {
        return false;
    }

    public function forceDelete(User $user, BerkasBukti $berkas): bool
    {
        return false;
    }
}
