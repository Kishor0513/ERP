<?php

namespace App\Services\HR;

use App\Models\Artisan;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\PieceRate;
use App\Models\ProductionOrderAssignment;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function getAll(array $filters = [])
    {
        $query = PayrollRun::with(['items.artisan', 'approver']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['from_date'])) {
            $query->where('period_start', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('period_end', '<=', $filters['to_date']);
        }

        return $query->orderByDesc('period_start')->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): PayrollRun
    {
        return PayrollRun::with(['items.artisan', 'items.staffUser', 'approver'])->findOrFail($id);
    }

    public function create(array $data): PayrollRun
    {
        $data['status'] = $data['status'] ?? 'draft';

        return PayrollRun::create($data);
    }

    public function update(PayrollRun $payrollRun, array $data): PayrollRun
    {
        if (in_array($payrollRun->status, ['approved', 'paid'])) {
            throw new \Exception('Cannot update a '.$payrollRun->status.' payroll run.');
        }

        $payrollRun->update($data);

        return $payrollRun->fresh(['items.artisan', 'approver']);
    }

    public function runPayroll(PayrollRun $payrollRun): PayrollRun
    {
        if ($payrollRun->status !== 'draft') {
            throw new \Exception('Can only run payroll for draft runs.');
        }

        return DB::transaction(function () use ($payrollRun) {
            $artisans = Artisan::where('status', 'active')->get();

            $totalAmount = 0;

            foreach ($artisans as $artisan) {
                $assignments = ProductionOrderAssignment::where('artisan_id', $artisan->id)
                    ->where('status', 'completed')
                    ->whereHas('productionOrder', function ($q) use ($payrollRun) {
                        $q->whereBetween('due_date', [$payrollRun->period_start, $payrollRun->period_end]);
                    })
                    ->get();

                $grossAmount = 0;
                $unitsCompleted = 0;

                foreach ($assignments as $assignment) {
                    $pieceRate = PieceRate::where('product_id', $assignment->productionOrder->productVariant->product_id)
                        ->active()
                        ->first();

                    if ($pieceRate) {
                        $grossAmount += $assignment->qty_completed * $pieceRate->rate;
                    }

                    $unitsCompleted += $assignment->qty_completed;
                }

                if ($grossAmount > 0) {
                    $deductions = $grossAmount * 0.1;
                    $netAmount = $grossAmount - $deductions;

                    PayrollItem::create([
                        'payroll_run_id' => $payrollRun->id,
                        'artisan_id' => $artisan->id,
                        'gross_amount' => $grossAmount,
                        'deductions' => $deductions,
                        'net_amount' => $netAmount,
                        'units_completed' => $unitsCompleted,
                    ]);

                    $totalAmount += $netAmount;
                }
            }

            $payrollRun->update([
                'status' => 'calculating',
                'total_amount' => $totalAmount,
            ]);

            return $payrollRun->fresh(['items.artisan', 'approver']);
        });
    }

    public function approve(PayrollRun $payrollRun): PayrollRun
    {
        if ($payrollRun->status !== 'calculating') {
            throw new \Exception('Can only approve calculating payroll runs.');
        }

        $payrollRun->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return $payrollRun->fresh(['items.artisan', 'approver']);
    }

    public function markPaid(PayrollRun $payrollRun): PayrollRun
    {
        if ($payrollRun->status !== 'approved') {
            throw new \Exception('Can only mark approved payroll runs as paid.');
        }

        $payrollRun->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $payrollRun->fresh(['items.artisan', 'approver']);
    }

    public function export(PayrollRun $payrollRun): array
    {
        if ($payrollRun->status === 'draft') {
            throw new \Exception('Cannot export draft payroll runs.');
        }

        $payrollRun->load(['items.artisan', 'items.staffUser']);

        $exportData = [
            'period' => [
                'start' => $payrollRun->period_start->format('Y-m-d'),
                'end' => $payrollRun->period_end->format('Y-m-d'),
            ],
            'total_amount' => $payrollRun->total_amount,
            'items' => $payrollRun->items->map(function ($item) {
                return [
                    'artisan' => $item->artisan?->name ?? $item->staffUser?->name,
                    'gross_amount' => $item->gross_amount,
                    'deductions' => $item->deductions,
                    'net_amount' => $item->net_amount,
                    'units_completed' => $item->units_completed,
                ];
            }),
        ];

        return $exportData;
    }

    public function delete(PayrollRun $payrollRun): bool
    {
        if (in_array($payrollRun->status, ['approved', 'paid'])) {
            throw new \Exception('Cannot delete approved or paid payroll runs.');
        }

        return DB::transaction(function () use ($payrollRun) {
            $payrollRun->items()->delete();

            return $payrollRun->delete();
        });
    }
}
