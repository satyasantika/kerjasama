<?php

namespace App\Policies;

use App\Enums\Peran;
use App\Models\User;

/**
 * Pengelolaan pengguna hanya untuk super_admin. Akun tidak dihapus,
 * hanya dinonaktifkan agar jejak audit tetap utuh.
 */
class UserPolicy
{
    private function kelola(User $user): bool
    {
        return $user->hasRole(Peran::SuperAdmin->value);
    }

    public function viewAny(User $user): bool
    {
        return $this->kelola($user);
    }

    public function view(User $user, User $model): bool
    {
        return $this->kelola($user);
    }

    public function create(User $user): bool
    {
        return $this->kelola($user);
    }

    public function update(User $user, User $model): bool
    {
        return $this->kelola($user);
    }

    public function delete(User $user, User $model): bool
    {
        return false;
    }

    public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
