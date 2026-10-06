<?php

namespace App\Policies;

use App\Models\User;

class MitraPolicy extends MasterPolicy
{
    public function delete(User $user, $model): bool
    {
        return $this->kelola($user);
    }

    public function restore(User $user, $model): bool
    {
        return $this->kelola($user);
    }
}
