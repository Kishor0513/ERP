<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RawMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderItemFactory extends Factory
{
    protected $model = PurchaseOrderItem::class;

    public function definition(): array
    {
        $qty = fake()->randomFloat(4, 1, 100);
        $unitCost = fake()->randomFloat(2, 1, 500);

        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'raw_material_id' => RawMaterial::factory(),
            'qty_ordered' => $qty,
            'qty_received' => 0,
            'unit_cost' => $unitCost,
            'total' => $qty * $unitCost,
        ];
    }
}
