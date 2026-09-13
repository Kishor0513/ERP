<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Admin');
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('view_purchase_orders');
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->isAdmin($user) || $user->can('view_purchase_orders');
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('create_purchase_orders');
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->isAdmin($user) || $user->can('update_purchase_orders');
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->isAdmin($user) || $user->can('delete_purchase_orders');
    }

    public function receive(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->isAdmin($user) || $user->can('receive_purchase_orders');
    }
}
