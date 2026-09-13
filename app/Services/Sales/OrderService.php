<?php

namespace App\Services\Sales;

use App\Models\ProductionOrder;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;

class OrderService
{
    private const VALID_STATUS_TRANSITIONS = [
        'draft' => ['pending_payment', 'cancelled'],
        'pending_payment' => ['confirmed', 'cancelled'],
        'confirmed' => ['in_production', 'reserved', 'cancelled'],
        'in_production' => ['reserved', 'cancelled'],
        'reserved' => ['packed', 'cancelled'],
        'packed' => ['shipped', 'cancelled'],
        'shipped' => ['delivered', 'cancelled'],
        'delivered' => ['closed'],
        'closed' => [],
        'cancelled' => ['draft'],
        'refunded' => [],
    ];

    public function getAll(array $filters = [])
    {
        $query = SalesOrder::with(['wholesaleAccount', 'lead', 'creator', 'items.productVariant.product']);

        if (isset($filters['channel'])) {
            $query->where('channel', $filters['channel']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['wholesale_account_id'])) {
            $query->where('wholesale_account_id', $filters['wholesale_account_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('order_number', 'like', "%{$filters['search']}%")
                    ->orWhere('po_number', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderByDesc('created_at')->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): SalesOrder
    {
        return SalesOrder::with([
            'wholesaleAccount',
            'lead',
            'creator',
            'items.productVariant.product',
            'items.productVariant.colorChartEntry',
            'productionOrders',
            'shipments',
            'invoices.payments',
        ])->findOrFail($id);
    }

    public function create(array $data): SalesOrder
    {
        return DB::transaction(function () use ($data) {
            $data['order_number'] = $this->generateOrderNumber();
            $data['created_by'] = auth()->id();
            $data['status'] = $data['status'] ?? 'draft';

            $order = SalesOrder::create($data);

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $itemData['total'] = $itemData['qty'] * $itemData['unit_price'];
                    $order->items()->create($itemData);
                }

                $this->recalculateTotals($order);
            }

            return $order->load(['wholesaleAccount', 'lead', 'creator', 'items.productVariant.product']);
        });
    }

    public function update(SalesOrder $order, array $data): SalesOrder
    {
        if (! in_array($order->status, ['draft'])) {
            throw new \Exception('Only draft orders can be edited.');
        }

        return DB::transaction(function () use ($order, $data) {
            $order->update(collect($data)->except('items')->toArray());

            if (isset($data['items'])) {
                $order->items()->delete();

                foreach ($data['items'] as $itemData) {
                    $itemData['total'] = $itemData['qty'] * $itemData['unit_price'];
                    $order->items()->create($itemData);
                }

                $this->recalculateTotals($order);
            }

            return $order->fresh(['wholesaleAccount', 'lead', 'creator', 'items.productVariant.product']);
        });
    }

    public function updateStatus(SalesOrder $order, string $newStatus): SalesOrder
    {
        $validTransitions = self::VALID_STATUS_TRANSITIONS[$order->status] ?? [];

        if (! in_array($newStatus, $validTransitions)) {
            throw new \Exception(
                "Cannot transition from '{$order->status}' to '{$newStatus}'. Valid transitions: ".
                implode(', ', $validTransitions)
            );
        }

        $order->update(['status' => $newStatus]);

        if ($newStatus === 'confirmed') {
            $this->createProductionOrders($order);
        }

        return $order->fresh(['wholesaleAccount', 'lead', 'creator', 'items.productVariant.product']);
    }

    public function delete(SalesOrder $order): bool
    {
        if (! in_array($order->status, ['draft'])) {
            throw new \Exception('Only draft orders can be deleted.');
        }

        return DB::transaction(function () use ($order) {
            $order->items()->delete();

            return $order->delete();
        });
    }

    protected function createProductionOrders(SalesOrder $order): void
    {
        foreach ($order->items as $item) {
            ProductionOrder::create([
                'production_order_number' => $this->generateProductionOrderNumber(),
                'sales_order_id' => $order->id,
                'product_variant_id' => $item->product_variant_id,
                'qty_ordered' => $item->qty,
                'qty_completed' => 0,
                'status' => 'pending',
                'due_date' => now()->addDays(14),
            ]);
        }
    }

    protected function recalculateTotals(SalesOrder $order): void
    {
        $subtotal = $order->items->sum('total');
        $tax = $subtotal * 0.1;
        $discount = $order->discount ?? 0;
        $shippingCost = $order->shipping_cost ?? 0;
        $total = $subtotal + $tax - $discount + $shippingCost;

        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);
    }

    protected function generateOrderNumber(): string
    {
        $prefix = 'SO-'.now()->format('Ym');
        $lastOrder = SalesOrder::where('order_number', 'like', $prefix.'%')
            ->orderByDesc('order_number')
            ->first();

        if ($lastOrder && preg_match('/(\d+)$/', $lastOrder->order_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        return $prefix.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    protected function generateProductionOrderNumber(): string
    {
        $prefix = 'PRD-'.now()->format('Ym');
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
