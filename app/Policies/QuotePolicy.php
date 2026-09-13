<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;

class QuotePolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Admin');
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('view_quotes');
    }

    public function view(User $user, Quote $quote): bool
    {
        return $this->isAdmin($user) || $user->can('view_quotes');
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('create_quotes');
    }

    public function update(User $user, Quote $quote): bool
    {
        return $this->isAdmin($user) || $user->can('update_quotes');
    }

    public function delete(User $user, Quote $quote): bool
    {
        return $this->isAdmin($user) || $user->can('delete_quotes');
    }

    public function convert(User $user, Quote $quote): bool
    {
        return $this->isAdmin($user) || $user->can('convert_quotes');
    }
}
