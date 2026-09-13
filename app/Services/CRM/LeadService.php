<?php

namespace App\Services\CRM;

use App\Models\Lead;
use App\Models\WholesaleAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeadService
{
    private const VALID_STATUS_TRANSITIONS = [
        'new' => ['contacted', 'qualified', 'lost'],
        'contacted' => ['qualified', 'lost'],
        'qualified' => ['converted', 'lost'],
        'converted' => [],
        'lost' => ['new'],
    ];

    public function getAll(array $filters = [])
    {
        $query = Lead::with(['assignee', 'wholesaleAccount', 'quotes']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
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

    public function getById(int $id): Lead
    {
        return Lead::with(['assignee', 'wholesaleAccount', 'quotes', 'communications'])->findOrFail($id);
    }

    public function create(array $data): Lead
    {
        $data['status'] = $data['status'] ?? 'new';
        $data['assigned_to'] = $data['assigned_to'] ?? Auth::id();

        return Lead::create($data);
    }

    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);

        return $lead->fresh(['assignee', 'wholesaleAccount', 'quotes']);
    }

    public function updateStatus(Lead $lead, string $newStatus): Lead
    {
        $validTransitions = self::VALID_STATUS_TRANSITIONS[$lead->status] ?? [];

        if (! in_array($newStatus, $validTransitions)) {
            throw new \Exception(
                "Cannot transition from '{$lead->status}' to '{$newStatus}'. Valid transitions: ".
                implode(', ', $validTransitions)
            );
        }

        $lead->update(['status' => $newStatus]);

        return $lead->fresh(['assignee', 'wholesaleAccount', 'quotes']);
    }

    public function assign(Lead $lead, int $userId): Lead
    {
        $lead->update(['assigned_to' => $userId]);

        return $lead->fresh('assignee');
    }

    public function convert(Lead $lead, array $accountData = []): WholesaleAccount
    {
        if ($lead->status === 'converted') {
            throw new \Exception('Lead is already converted.');
        }

        if ($lead->status !== 'qualified') {
            throw new \Exception('Only qualified leads can be converted.');
        }

        return DB::transaction(function () use ($lead, $accountData) {
            $defaultAccountData = [
                'company_name' => $lead->company_name,
                'contact_name' => $lead->contact_name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'status' => 'pending',
            ];

            $mergedData = array_merge($defaultAccountData, $accountData);

            $account = WholesaleAccount::create($mergedData);

            $lead->update([
                'status' => 'converted',
                'converted_to_account_id' => $account->id,
            ]);

            return $account;
        });
    }

    public function getPipeline(): array
    {
        return Lead::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    public function delete(Lead $lead): bool
    {
        if ($lead->status === 'converted') {
            throw new \Exception('Cannot delete a converted lead.');
        }

        return $lead->delete();
    }
}
