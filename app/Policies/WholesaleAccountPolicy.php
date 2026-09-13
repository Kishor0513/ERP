<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WholesaleAccount;

class WholesaleAccountPolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Admin');
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('view_wholesale_accounts');
    }

    public function view(User $user, WholesaleAccount $wholesaleAccount): bool
    {
        return $this->isAdmin($user) || $user->can('view_wholesale_accounts');
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('create_wholesale_accounts');
    }

    public function update(User $user, WholesaleAccount $wholesaleAccount): bool
    {
        return $this->isAdmin($user) || $user->can('update_wholesale_accounts');
    }

    public function delete(User $user, WholesaleAccount $wholesaleAccount): bool
    {
        return $this->isAdmin($user) || $user->can('delete_wholesale_accounts');
    }

    public function approve(User $user, WholesaleAccount $wholesaleAccount): bool
    {
        return $this->isAdmin($user) || $user->can('approve_wholesale_accounts');
    }
}
