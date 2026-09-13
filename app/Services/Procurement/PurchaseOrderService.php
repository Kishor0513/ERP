<?php

namespace App\Services\Procurement;

use App\Models\GoodsReceiptItem;
use App\Models\GoodsReceiptNote;
use App\Models\PurchaseOrder;
use App\Models\RawMaterial;
use App\Models\RawMaterialBatch;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    private const VALID_STATUS_TRANSITIONS = [
        'draft' => ['submitted', 'cancelled'],
        'submitted' => ['confirmed', 'cancelled'],
        'confirmed' => ['partially_received', 'received', 'cancelled'],
        'partially_received' => ['received'],
        'received' => [],
        'cancelled' => [],
    ];

    public function getAll(array $filters = [])
    {
        $query = PurchaseOrder::with(['supplier', 'creator', 'items.rawMaterial', 'goodsReceiptNotes']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (isset($filters['search'])) {
            $query->where('po_number', 'like', "%{$filters['search']}%");
        }

        return $query->orderByDesc('created_at')->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): PurchaseOrder
    {
        return PurchaseOrder::with([
            'supplier',
            'creator',
            'items.rawMaterial',
            'goodsReceiptNotes.items.rawMaterial',
            'goodsReceiptNotes.receiver',
        ])->findOrFail($id);
    }

    public function create(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $data['po_number'] = $this->generatePoNumber();
            $data['created_by'] = auth()->id();
            $data['status'] = $data['status'] ?? 'draft';

            $order = PurchaseOrder::create($data);

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $itemData['total'] = $itemData['qty_ordered'] * $itemData['unit_cost'];
                    $order->items()->create($itemData);
                }

                $this->recalculateTotals($order);
            }

            return $order->load(['supplier', 'creator', 'items.rawMaterial']);
        });
    }

    public function update(PurchaseOrder $order, array $data): PurchaseOrder
    {
        if (! in_array($order->status, ['draft'])) {
            throw new \Exception('Only draft purchase orders can be edited.');
        }

        return DB::transaction(function () use ($order, $data) {
            $order->update(collect($data)->except('items')->toArray());

            if (isset($data['items'])) {
                $order->items()->delete();

                foreach ($data['items'] as $itemData) {
                    $itemData['total'] = $itemData['qty_ordered'] * $itemData['unit_cost'];
                    $order->items()->create($itemData);
                }

                $this->recalculateTotals($order);
            }

            return $order->fresh(['supplier', 'creator', 'items.rawMaterial']);
        });
    }

    public function updateStatus(PurchaseOrder $order, string $newStatus): PurchaseOrder
    {
        $validTransitions = self::VALID_STATUS_TRANSITIONS[$order->status] ?? [];

        if (! in_array($newStatus, $validTransitions)) {
            throw new \Exception(
                "Cannot transition from '{$order->status}' to '{$newStatus}'. Valid transitions: ".
                implode(', ', $validTransitions)
            );
        }

        $order->update(['status' => $newStatus]);

        return $order->fresh(['supplier', 'creator', 'items.rawMaterial']);
    }

    public function receive(PurchaseOrder $order, array $receivedItems, ?string $notes = null): GoodsReceiptNote
    {
        if (in_array($order->status, ['received', 'cancelled'])) {
            throw new \Exception('Cannot receive items for a '.$order->status.' order.');
        }

        return DB::transaction(function () use ($order, $receivedItems, $notes) {
            $grn = GoodsReceiptNote::create([
                'grn_number' => $this->generateGrnNumber(),
                'purchase_order_id' => $order->id,
                'received_by' => auth()->id(),
                'received_at' => now(),
                'notes' => $notes,
            ]);

            foreach ($receivedItems as $itemData) {
                $poItem = $order->items()->findOrFail($itemData['purchase_order_item_id']);

                $qtyReceived = $itemData['qty_received'];

                if (($poItem->qty_received + $qtyReceived) > $poItem->qty_ordered) {
                    throw new \Exception(
                        "Cannot receive more than ordered for item {$poItem->id}. ".
                        "Ordered: {$poItem->qty_ordered}, Already received: {$poItem->qty_received}"
                    );
                }

                $poItem->increment('qty_received', $qtyReceived);

                GoodsReceiptItem::create([
                    'grn_id' => $grn->id,
                    'purchase_order_item_id' => $poItem->id,
                    'raw_material_id' => $poItem->raw_material_id,
                    'qty_received' => $qtyReceived,
                    'unit_cost' => $poItem->unit_cost,
                ]);

                $batch = RawMaterialBatch::create([
                    'raw_material_id' => $poItem->raw_material_id,
                    'supplier_id' => $order->supplier_id,
                    'batch_no' => $grn->grn_number,
                    'qty_received' => $qtyReceived,
                    'qty_remaining' => $qtyReceived,
                    'unit_cost' => $poItem->unit_cost,
                    'received_at' => now(),
                ]);

                StockMovement::create([
                    'item_type' => RawMaterial::class,
                    'item_id' => $poItem->raw_material_id,
                    'warehouse_id' => 1,
                    'type' => 'purchase',
                    'qty' => $qtyReceived,
                    'reference_type' => GoodsReceiptNote::class,
                    'reference_id' => $grn->id,
                    'notes' => "GRN #{$grn->grn_number}",
                    'created_by' => auth()->id(),
                ]);

                RawMaterial::where('id', $poItem->raw_material_id)
                    ->increment('current_stock', $qtyReceived);
            }

            $allReceived = $order->items->every(
                fn ($item) => $item->fresh()->qty_received >= $item->qty_ordered
            );

            $order->update(['status' => $allReceived ? 'received' : 'partially_received']);

            return $grn->load(['items.rawMaterial', 'receiver']);
        });
    }

    public function delete(PurchaseOrder $order): bool
    {
        if (! in_array($order->status, ['draft'])) {
            throw new \Exception('Only draft purchase orders can be deleted.');
        }

        return DB::transaction(function () use ($order) {
            $order->items()->delete();

            return $order->delete();
        });
    }

    protected function recalculateTotals(PurchaseOrder $order): void
    {
        $subtotal = $order->items->sum('total');
        $order->update(['subtotal' => $subtotal, 'total' => $subtotal]);
    }

    protected function generatePoNumber(): string
    {
        $prefix = 'PO-'.now()->format('Ym');
        $lastOrder = PurchaseOrder::where('po_number', 'like', $prefix.'%')
            ->orderByDesc('po_number')
            ->first();

        if ($lastOrder && preg_match('/(\d+)$/', $lastOrder->po_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        return $prefix.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    protected function generateGrnNumber(): string
    {
        $prefix = 'GRN-'.now()->format('Ym');
        $lastGrn = GoodsReceiptNote::where('grn_number', 'like', $prefix.'%')
            ->orderByDesc('grn_number')
            ->first();

        if ($lastGrn && preg_match('/(\d+)$/', $lastGrn->grn_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        return $prefix.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
