<?php

namespace App\Services\Production;

use App\Models\Artisan;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderAssignment;
use Illuminate\Support\Facades\DB;

class ProductionOrderService
{
    private const VALID_STATUS_TRANSITIONS = [
        'pending' => ['in_progress', 'cancelled'],
        'in_progress' => ['completed', 'on_hold', 'cancelled'],
        'on_hold' => ['in_progress', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function getAll(array $filters = [])
    {
        $query = ProductionOrder::with(['salesOrder', 'productVariant.product', 'assignments.artisan', 'qcInspections']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['sales_order_id'])) {
            $query->where('sales_order_id', $filters['sales_order_id']);
        }

        if (isset($filters['artisan_id'])) {
            $query->whereHas('assignments', fn ($q) => $q->where('artisan_id', $filters['artisan_id']));
        }

        if (isset($filters['overdue'])) {
            $query->where('status', '!=', 'completed')->where('due_date', '<', now());
        }

        return $query->orderBy('due_date')->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): ProductionOrder
    {
        return ProductionOrder::with([
            'salesOrder',
            'productVariant.product',
            'productVariant.colorChartEntry',
            'assignments.artisan',
            'qcInspections.inspector',
        ])->findOrFail($id);
    }

    public function create(array $data): ProductionOrder
    {
        return DB::transaction(function () use ($data) {
            $data['production_order_number'] = $this->generateOrderNumber();

            $order = ProductionOrder::create($data);

            return $order->load(['salesOrder', 'productVariant.product']);
        });
    }

    public function update(ProductionOrder $order, array $data): ProductionOrder
    {
        if (in_array($order->status, ['completed', 'cancelled'])) {
            throw new \Exception('Cannot update a '.$order->status.' production order.');
        }

        $order->update($data);

        return $order->fresh(['salesOrder', 'productVariant.product', 'assignments.artisan']);
    }

    public function updateStatus(ProductionOrder $order, string $newStatus): ProductionOrder
    {
        $validTransitions = self::VALID_STATUS_TRANSITIONS[$order->status] ?? [];

        if (! in_array($newStatus, $validTransitions)) {
            throw new \Exception(
                "Cannot transition from '{$order->status}' to '{$newStatus}'. Valid transitions: ".
                implode(', ', $validTransitions)
            );
        }

        return DB::transaction(function () use ($order, $newStatus) {
            $order->update(['status' => $newStatus]);

            if ($newStatus === 'completed') {
                $this->completeOrder($order);
            }

            return $order->fresh(['salesOrder', 'productVariant.product', 'assignments.artisan']);
        });
    }

    public function assignArtisan(ProductionOrder $order, int $artisanId, int $qty): ProductionOrderAssignment
    {
        if (in_array($order->status, ['completed', 'cancelled'])) {
            throw new \Exception('Cannot assign artisan to a '.$order->status.' production order.');
        }

        $totalAssigned = $order->assignments->sum('qty_assigned');
        if (($totalAssigned + $qty) > $order->qty_ordered) {
            throw new \Exception(
                'Cannot assign more than ordered quantity. Ordered: {$order->qty_ordered}, Already assigned: {$totalAssigned}'
            );
        }

        $artisan = Artisan::findOrFail($artisanId);

        if ($artisan->status !== 'active') {
            throw new \Exception('Artisan is not active.');
        }

        return ProductionOrderAssignment::create([
            'production_order_id' => $order->id,
            'artisan_id' => $artisanId,
            'qty_assigned' => $qty,
            'qty_completed' => 0,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);
    }

    public function updateAssignment(ProductionOrderAssignment $assignment, array $data): ProductionOrderAssignment
    {
        $assignment->update($data);

        if (isset($data['qty_completed'])) {
            $this->recalculateCompletedQty($assignment->productionOrder);
        }

        return $assignment->fresh(['artisan']);
    }

    public function getCapacityPlanning(array $filters = []): array
    {
        $fromDate = $filters['from_date'] ?? now()->startOfWeek();
        $toDate = $filters['to_date'] ?? now()->endOfWeek();

        $artisans = Artisan::where('status', 'active')->get();

        $assignments = ProductionOrderAssignment::with(['productionOrder.productVariant.product', 'artisan'])
            ->whereHas('productionOrder', function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('due_date', [$fromDate, $toDate])
                    ->whereNotIn('status', ['completed', 'cancelled']);
            })
            ->get()
            ->groupBy('artisan_id');

        $capacity = [];
        foreach ($artisans as $artisan) {
            $artisanAssignments = $assignments->get($artisan->id, collect());
            $capacity[] = [
                'artisan' => $artisan,
                'total_assigned' => $artisanAssignments->sum('qty_assigned'),
                'total_completed' => $artisanAssignments->sum('qty_completed'),
                'assignments' => $artisanAssignments,
            ];
        }

        return $capacity;
    }

    protected function completeOrder(ProductionOrder $order): void
    {
        $totalCompleted = $order->assignments->sum('qty_completed');

        if ($totalCompleted < $order->qty_ordered) {
            throw new \Exception(
                "Cannot complete order. Assigned qty completed ({$totalCompleted}) is less than ordered ({$order->qty_ordered})."
            );
        }

        $order->update(['qty_completed' => $totalCompleted]);
    }

    protected function recalculateCompletedQty(ProductionOrder $order): void
    {
        $totalCompleted = $order->assignments->sum('qty_completed');
        $order->update(['qty_completed' => $totalCompleted]);
    }

    protected function generateOrderNumber(): string
    {
        $prefix = 'PO-'.now()->format('Ym');
        $lastOrder = ProductionOrder::where('production_order_number', 'like', $prefix.'%')
            ->orderByDesc('production_order_number')
            ->first();

        if ($lastOrder && preg_match('/(\d+)$/', $lastOrder->production_order_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        return $prefix.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
