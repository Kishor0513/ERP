<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Admin');
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('view_invoices');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->isAdmin($user) || $user->can('view_invoices');
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('create_invoices');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->isAdmin($user) || $user->can('update_invoices');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->isAdmin($user) || $user->can('delete_invoices');
    }

    public function recordPayment(User $user, Invoice $invoice): bool
    {
        return $this->isAdmin($user) || $user->can('record_payments');
    }
}
