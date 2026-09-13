<?php

namespace Database\Seeders;

use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    public function run(): void
    {
        $movements = [
            [
                'item_type' => 'raw_material',
                'item_id' => 1,
                'warehouse_id' => 1,
                'type' => 'receipt',
                'qty' => 100.0000,
                'reference_type' => 'App\Models\PurchaseOrder',
                'reference_id' => 1,
                'notes' => 'Received from Canterbury Wool Traders PO-2024-001.',
                'created_by' => 4,
            ],
            [
                'item_type' => 'raw_material',
                'item_id' => 2,
                'warehouse_id' => 1,
                'type' => 'receipt',
                'qty' => 50.0000,
                'reference_type' => 'App\Models\PurchaseOrder',
                'reference_id' => 1,
                'notes' => 'Received from Canterbury Wool Traders PO-2024-001.',
                'created_by' => 4,
            ],
            [
                'item_type' => 'raw_material',
                'item_id' => 3,
                'warehouse_id' => 1,
                'type' => 'receipt',
                'qty' => 10.0000,
                'reference_type' => 'App\Models\PurchaseOrder',
                'reference_id' => 1,
                'notes' => 'Received from Canterbury Wool Traders PO-2024-001.',
                'created_by' => 4,
            ],
            [
                'item_type' => 'raw_material',
                'item_id' => 1,
                'warehouse_id' => 2,
                'type' => 'consumption',
                'qty' => -20.0000,
                'reference_type' => 'App\Models\ProductionOrder',
                'reference_id' => 1,
                'notes' => 'Consumed for Production Order PO-2024-001.',
                'created_by' => 2,
            ],
            [
                'item_type' => 'raw_material',
                'item_id' => 3,
                'warehouse_id' => 1,
                'type' => 'consumption',
                'qty' => -5.0000,
                'reference_type' => 'App\Models\ProductionOrder',
                'reference_id' => 3,
                'notes' => 'Consumed for Production Order PO-2024-003.',
                'created_by' => 2,
            ],
            [
                'item_type' => 'product_variant',
                'item_id' => 1,
                'warehouse_id' => 3,
                'type' => 'output',
                'qty' => 100.0000,
                'reference_type' => 'App\Models\ProductionOrder',
                'reference_id' => 3,
                'notes' => 'Output from Production Order PO-2024-003.',
                'created_by' => 2,
            ],
            [
                'item_type' => 'product_variant',
                'item_id' => 1,
                'warehouse_id' => 3,
                'type' => 'dispatch',
                'qty' => -100.0000,
                'reference_type' => 'App\Models\Shipment',
                'reference_id' => 1,
                'notes' => 'Dispatched via SHP-2024-001.',
                'created_by' => 8,
            ],
            [
                'item_type' => 'product_variant',
                'item_id' => 10,
                'warehouse_id' => 3,
                'type' => 'adjustment',
                'qty' => 10.0000,
                'reference_type' => null,
                'reference_id' => null,
                'notes' => 'Stock adjustment after recount.',
                'created_by' => 4,
            ],
        ];

        foreach ($movements as $movement) {
            StockMovement::create($movement);
        }
    }
}
