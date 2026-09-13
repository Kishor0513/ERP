<?php

namespace App\Services\Inventory;

use App\Models\RawMaterial;
use App\Models\RawMaterialBatch;
use Illuminate\Support\Facades\DB;

class RawMaterialService
{
    public function getAll(array $filters = [])
    {
        $query = RawMaterial::with('batches.supplier');

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('sku', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['below_reorder'])) {
            $query->whereColumn('current_stock', '<=', 'reorder_point');
        }

        return $query->orderBy('name')->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): RawMaterial
    {
        return RawMaterial::with(['batches.supplier', 'boms.product'])->findOrFail($id);
    }

    public function create(array $data): RawMaterial
    {
        return RawMaterial::create($data);
    }

    public function update(RawMaterial $material, array $data): RawMaterial
    {
        $material->update($data);

        return $material;
    }

    public function delete(RawMaterial $material): bool
    {
        if ($material->batches()->exists()) {
            throw new \Exception('Cannot delete raw material with existing batches.');
        }

        return $material->delete();
    }

    public function createBatch(array $data): RawMaterialBatch
    {
        return DB::transaction(function () use ($data) {
            $batch = RawMaterialBatch::create($data);

            $material = RawMaterial::findOrFail($data['raw_material_id']);
            $material->increment('current_stock', $data['qty_received']);

            return $batch->load(['rawMaterial', 'supplier']);
        });
    }

    public function updateBatch(RawMaterialBatch $batch, array $data): RawMaterialBatch
    {
        return DB::transaction(function () use ($batch, $data) {
            $oldQty = $batch->qty_remaining;
            $batch->update($data);
            $newQty = $batch->qty_remaining;

            $material = $batch->rawMaterial;
            $material->increment('current_stock', $newQty - $oldQty);

            return $batch->load(['rawMaterial', 'supplier']);
        });
    }

    public function deductBatch(RawMaterialBatch $batch, int $qty): RawMaterialBatch
    {
        if ($batch->qty_remaining < $qty) {
            throw new \Exception('Insufficient batch quantity. Available: '.$batch->qty_remaining);
        }

        return DB::transaction(function () use ($batch, $qty) {
            $batch->decrement('qty_remaining', $qty);

            $material = $batch->rawMaterial;
            $material->decrement('current_stock', $qty);

            return $batch->fresh(['rawMaterial', 'supplier']);
        });
    }

    public function getBelowReorderPoint()
    {
        return RawMaterial::whereColumn('current_stock', '<=', 'reorder_point')
            ->orderBy('current_stock')
            ->get();
    }
}
