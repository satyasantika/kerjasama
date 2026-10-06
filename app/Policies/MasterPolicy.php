<?php

namespace App\Policies;

use App\Enums\Peran;
use App\Models\User;

/**
 * Dasar kebijakan data master: semua peran boleh melihat, hanya
 * super_admin dan admin_fakultas yang boleh mengubah. Penghapusan
 * permanen tidak pernah diizinkan.
 */
abstract class MasterPolicy
{
    protected function kelola(User $user): bool
    {
        return $user->hasAnyRole([Peran::SuperAdmin->value, Peran::AdminFakultas->value]);
    }

    public function viewAny(User $user): bool
    {
        return $user->roles()->exists();
    }

    public function view(User $user, $model): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->kelola($user);
    }

    public function update(User $user, $model): bool
    {
        return $this->kelola($user);
    }

    public function delete(User $user, $model): bool
    {
        return false;
    }

    public function restore(User $user, $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, $model): bool
    {
        return false;
    }
}
