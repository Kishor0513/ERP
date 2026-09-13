<?php

namespace App\Policies;

use App\Models\ProductionOrder;
use App\Models\User;

class ProductionOrderPolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Admin');
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('view_production_orders');
    }

    public function view(User $user, ProductionOrder $productionOrder): bool
    {
        return $this->isAdmin($user) || $user->can('view_production_orders');
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('create_production_orders');
    }

    public function update(User $user, ProductionOrder $productionOrder): bool
    {
        return $this->isAdmin($user) || $user->can('update_production_orders');
    }

    public function delete(User $user, ProductionOrder $productionOrder): bool
    {
        return $this->isAdmin($user) || $user->can('delete_production_orders');
    }

    public function assign(User $user, ProductionOrder $productionOrder): bool
    {
        return $this->isAdmin($user) || $user->can('assign_production_orders');
    }
}
