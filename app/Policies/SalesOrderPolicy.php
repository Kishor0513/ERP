<?php

namespace App\Policies;

use App\Models\SalesOrder;
use App\Models\User;

class SalesOrderPolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Admin');
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('view_sales_orders');
    }

    public function view(User $user, SalesOrder $salesOrder): bool
    {
        return $this->isAdmin($user) || $user->can('view_sales_orders');
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('create_sales_orders');
    }

    public function update(User $user, SalesOrder $salesOrder): bool
    {
        return $this->isAdmin($user) || $user->can('update_sales_orders');
    }

    public function delete(User $user, SalesOrder $salesOrder): bool
    {
        return $this->isAdmin($user) || $user->can('delete_sales_orders');
    }

    public function updateStatus(User $user, SalesOrder $salesOrder): bool
    {
        return $this->isAdmin($user) || $user->can('update_sales_orders');
    }
}
