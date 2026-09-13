<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Admin');
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('view_shipments');
    }

    public function view(User $user, Shipment $shipment): bool
    {
        return $this->isAdmin($user) || $user->can('view_shipments');
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('create_shipments');
    }

    public function update(User $user, Shipment $shipment): bool
    {
        return $this->isAdmin($user) || $user->can('update_shipments');
    }

    public function delete(User $user, Shipment $shipment): bool
    {
        return $this->isAdmin($user) || $user->can('delete_shipments');
    }

    public function dispatch(User $user, Shipment $shipment): bool
    {
        return $this->isAdmin($user) || $user->can('dispatch_shipments');
    }

    public function deliver(User $user, Shipment $shipment): bool
    {
        return $this->isAdmin($user) || $user->can('deliver_shipments');
    }
}
