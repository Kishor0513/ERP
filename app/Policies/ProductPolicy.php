<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Admin');
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('view_products');
    }

    public function view(User $user, Product $product): bool
    {
        return $this->isAdmin($user) || $user->can('view_products');
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('create_products');
    }

    public function update(User $user, Product $product): bool
    {
        return $this->isAdmin($user) || $user->can('update_products');
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->isAdmin($user) || $user->can('delete_products');
    }

    public function import(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('import_products');
    }
}
