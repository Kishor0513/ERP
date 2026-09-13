<?php

namespace App\Services\Sales;

use App\Models\WholesaleAccount;
use Illuminate\Support\Facades\Auth;

class WholesaleService
{
    public function getAll(array $filters = [])
    {
        $query = WholesaleAccount::with(['approver', 'salesOrders']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('company_name', 'like', "%{$filters['search']}%")
                    ->orWhere('contact_name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderByDesc('created_at')->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): WholesaleAccount
    {
        return WholesaleAccount::with([
            'approver',
            'salesOrders' => fn ($q) => $q->orderByDesc('created_at')->limit(10),
            'quotes' => fn ($q) => $q->orderByDesc('created_at')->limit(10),
            'documents',
            'communications',
        ])->findOrFail($id);
    }

    public function create(array $data): WholesaleAccount
    {
        $data['status'] = $data['status'] ?? 'pending';

        return WholesaleAccount::create($data);
    }

    public function update(WholesaleAccount $account, array $data): WholesaleAccount
    {
        $account->update($data);

        return $account->fresh(['approver', 'documents']);
    }

    public function approve(WholesaleAccount $account): WholesaleAccount
    {
        if ($account->status === 'approved') {
            throw new \Exception('Account is already approved.');
        }

        $account->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return $account->fresh(['approver']);
    }

    public function reject(WholesaleAccount $account, ?string $reason = null): WholesaleAccount
    {
        if ($account->status === 'rejected') {
            throw new \Exception('Account is already rejected.');
        }

        $account->update([
            'status' => 'rejected',
            'notes' => $reason ? ($account->notes."\nRejection reason: ".$reason) : $account->notes,
        ]);

        return $account;
    }

    public function suspend(WholesaleAccount $account, ?string $reason = null): WholesaleAccount
    {
        $account->update([
            'status' => 'suspended',
            'notes' => $reason ? ($account->notes."\nSuspension reason: ".$reason) : $account->notes,
        ]);

        return $account;
    }

    public function delete(WholesaleAccount $account): bool
    {
        if ($account->salesOrders()->exists()) {
            throw new \Exception('Cannot delete account with existing orders.');
        }

        return $account->delete();
    }
}
