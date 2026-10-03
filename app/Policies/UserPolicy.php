<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isManager();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isManager();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isManager() && $user->isNot($model);
    }

    public function resetPassword(User $user, User $model): bool
    {
        return $user->isManager() && $user->isNot($model);
    }
}
