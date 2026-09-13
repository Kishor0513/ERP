<?php

namespace App\Services\Inventory;

use App\Models\ProductVariant;
use App\Models\RawMaterial;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function getMovements(array $filters = [])
    {
        $query = StockMovement::with(['warehouse', 'creator', 'itemable']);

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (isset($filters['item_type'])) {
            $query->where('item_type', $filters['item_type']);
        }

        if (isset($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        return $query->orderByDesc('created_at')->paginate($filters['per_page'] ?? 15);
    }

    public function createMovement(array $data): StockMovement
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id();

            $movement = StockMovement::create($data);

            $this->updateStockLevel($data['item_type'], $data['item_id'], $data['type'], $data['qty']);

            return $movement->load(['warehouse', 'creator', 'itemable']);
        });
    }

    public function adjustStock(string $itemType, int $itemId, int $warehouseId, int $adjustmentQty, string $reason): StockMovement
    {
        return $this->createMovement([
            'item_type' => $itemType,
            'item_id' => $itemId,
            'warehouse_id' => $warehouseId,
            'type' => 'adjustment',
            'qty' => abs($adjustmentQty),
            'notes' => $reason,
        ]);
    }

    public function transferStock(
        string $itemType,
        int $itemId,
        int $fromWarehouseId,
        int $toWarehouseId,
        int $qty,
        string $reason
    ): array {
        return DB::transaction(function () use ($itemType, $itemId, $fromWarehouseId, $toWarehouseId, $qty, $reason) {
            $outMovement = $this->createMovement([
                'item_type' => $itemType,
                'item_id' => $itemId,
                'warehouse_id' => $fromWarehouseId,
                'type' => 'transfer',
                'qty' => $qty,
                'notes' => $reason.' (Transfer out)',
            ]);

            $inMovement = $this->createMovement([
                'item_type' => $itemType,
                'item_id' => $itemId,
                'warehouse_id' => $toWarehouseId,
                'type' => 'transfer',
                'qty' => $qty,
                'notes' => $reason.' (Transfer in)',
            ]);

            return ['out' => $outMovement, 'in' => $inMovement];
        });
    }

    public function getLowStockAlerts(): array
    {
        $lowVariants = ProductVariant::where('is_active', true)
            ->where('stock_quantity', '<=', 10)
            ->get()
            ->map(fn ($v) => [
                'type' => 'product_variant',
                'id' => $v->id,
                'name' => $v->product->name.' ('.$v->full_sku.')',
                'current_stock' => $v->stock_quantity,
            ]);

        $lowMaterials = RawMaterial::whereColumn('current_stock', '<=', 'reorder_point')
            ->get()
            ->map(fn ($m) => [
                'type' => 'raw_material',
                'id' => $m->id,
                'name' => $m->name.' ('.$m->sku.')',
                'current_stock' => $m->current_stock,
                'reorder_point' => $m->reorder_point,
            ]);

        return ['product_variants' => $lowVariants, 'raw_materials' => $lowMaterials];
    }

    public function cycleCount(string $itemType, int $itemId, int $warehouseId, int $countedQty): StockMovement
    {
        $currentStock = $this->getCurrentStock($itemType, $itemId, $warehouseId);
        $adjustmentQty = $countedQty - $currentStock;

        if ($adjustmentQty == 0) {
            return null;
        }

        return $this->adjustStock(
            $itemType,
            $itemId,
            $warehouseId,
            $adjustmentQty,
            "Cycle count adjustment. Counted: {$countedQty}, System: {$currentStock}"
        );
    }

    public function getCurrentStock(string $itemType, int $itemId, int $warehouseId): int
    {
        $movements = StockMovement::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->get();

        $stock = 0;
        foreach ($movements as $movement) {
            $direction = $this->getMovementDirection($movement->item_type, $movement->type, $movement->notes);
            $stock += $direction * $movement->qty;
        }

        return $stock;
    }

    protected function updateStockLevel(string $itemType, int $itemId, string $type, int $qty): void
    {
        $direction = $this->getMovementDirection($itemType, $type, null);
        $delta = $direction * $qty;

        if ($itemType === 'product_variant') {
            $variant = ProductVariant::findOrFail($itemId);
            $variant->increment('stock_quantity', $delta);
        } elseif ($itemType === 'raw_material') {
            $material = RawMaterial::findOrFail($itemId);
            $material->increment('current_stock', $delta);
        }
    }

    protected function getMovementDirection(string $itemType, string $type, ?string $notes): int
    {
        return match ($type) {
            'receipt', 'output' => 1,
            'consumption', 'dispatch', 'return' => -1,
            'transfer' => str_contains($notes ?? '', 'Transfer in') ? 1 : -1,
            'adjustment' => str_contains($notes ?? '', 'Cycle count') || ($notes ?? '') !== '' ? 1 : -1,
            default => 1,
        };
    }
}
