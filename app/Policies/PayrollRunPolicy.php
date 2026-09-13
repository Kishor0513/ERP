<?php

namespace App\Policies;

use App\Models\PayrollRun;
use App\Models\User;

class PayrollRunPolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Admin');
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('view_payroll_runs');
    }

    public function view(User $user, PayrollRun $payrollRun): bool
    {
        return $this->isAdmin($user) || $user->can('view_payroll_runs');
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('create_payroll_runs');
    }

    public function update(User $user, PayrollRun $payrollRun): bool
    {
        return $this->isAdmin($user) || $user->can('update_payroll_runs');
    }

    public function delete(User $user, PayrollRun $payrollRun): bool
    {
        return $this->isAdmin($user) || $user->can('delete_payroll_runs');
    }

    public function approve(User $user, PayrollRun $payrollRun): bool
    {
        return $this->isAdmin($user) || $user->can('approve_payroll_runs');
    }

    public function export(User $user, PayrollRun $payrollRun): bool
    {
        return $this->isAdmin($user) || $user->can('export_payroll_runs');
    }
}
