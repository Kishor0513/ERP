<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Admin');
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('view_expenses');
    }

    public function view(User $user, Expense $expense): bool
    {
        return $this->isAdmin($user) || $user->can('view_expenses');
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('create_expenses');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $this->isAdmin($user) || $user->can('update_expenses');
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $this->isAdmin($user) || $user->can('delete_expenses');
    }

    public function approve(User $user, Expense $expense): bool
    {
        return $this->isAdmin($user) || $user->can('approve_expenses');
    }
}
