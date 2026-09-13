<?php

namespace App\Policies;

use App\Models\Artisan;
use App\Models\User;

class ArtisanPolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Admin');
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('view_artisans');
    }

    public function view(User $user, Artisan $artisan): bool
    {
        return $this->isAdmin($user) || $user->can('view_artisans');
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('create_artisans');
    }

    public function update(User $user, Artisan $artisan): bool
    {
        return $this->isAdmin($user) || $user->can('update_artisans');
    }

    public function delete(User $user, Artisan $artisan): bool
    {
        return $this->isAdmin($user) || $user->can('delete_artisans');
    }
}
