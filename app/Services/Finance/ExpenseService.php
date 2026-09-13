<?php

namespace App\Services\Finance;

use App\Models\Expense;
use Illuminate\Support\Facades\Auth;

class ExpenseService
{
    public function getAll(array $filters = [])
    {
        $query = Expense::with('approver');

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['from_date'])) {
            $query->where('incurred_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('incurred_at', '<=', $filters['to_date']);
        }

        if (isset($filters['approved_by'])) {
            $query->where('approved_by', $filters['approved_by']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('description', 'like', "%{$filters['search']}%")
                    ->orWhere('category', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderByDesc('incurred_at')->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): Expense
    {
        return Expense::with('approver')->findOrFail($id);
    }

    public function create(array $data): Expense
    {
        if (isset($data['receipt_path'])) {
            $data['receipt_path'] = $data['receipt_path']->store('receipts', 'public');
        }

        return Expense::create($data);
    }

    public function update(Expense $expense, array $data): Expense
    {
        if (isset($data['receipt_path']) && is_object($data['receipt_path'])) {
            $data['receipt_path'] = $data['receipt_path']->store('receipts', 'public');
        }

        $expense->update($data);

        return $expense->fresh('approver');
    }

    public function approve(Expense $expense): Expense
    {
        if ($expense->approved_by) {
            throw new \Exception('Expense is already approved.');
        }

        $expense->update([
            'approved_by' => Auth::id(),
        ]);

        return $expense->fresh('approver');
    }

    public function delete(Expense $expense): bool
    {
        if ($expense->approved_by) {
            throw new \Exception('Cannot delete an approved expense.');
        }

        return $expense->delete();
    }

    public function getSummary(array $filters = []): array
    {
        $query = Expense::query();

        if (isset($filters['from_date'])) {
            $query->where('incurred_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('incurred_at', '<=', $filters['to_date']);
        }

        $byCategory = (clone $query)
            ->select('category', \DB::raw('sum(amount) as total'), \DB::raw('count(*) as count'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $totalAmount = (clone $query)->sum('amount');

        return [
            'by_category' => $byCategory,
            'total_amount' => $totalAmount,
        ];
    }
}
