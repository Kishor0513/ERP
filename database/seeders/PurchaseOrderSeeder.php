<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = [
            [
                'order' => [
                    'po_number' => 'PO-2024-001',
                    'supplier_id' => 1,
                    'status' => 'received',
                    'expected_date' => now()->subDays(7)->format('Y-m-d'),
                    'subtotal' => 2500.00,
                    'total' => 2500.00,
                    'notes' => 'NZ Wool Roving bulk order. All items received.',
                    'created_by' => 4,
                ],
                'items' => [
                    ['raw_material_id' => 1, 'qty_ordered' => 100.0000, 'qty_received' => 100.0000, 'unit_cost' => 12.50, 'total' => 1250.00],
                    ['raw_material_id' => 2, 'qty_ordered' => 50.0000, 'qty_received' => 50.0000, 'unit_cost' => 15.00, 'total' => 750.00],
                    ['raw_material_id' => 3, 'qty_ordered' => 10.0000, 'qty_received' => 10.0000, 'unit_cost' => 50.00, 'total' => 500.00],
                ],
            ],
            [
                'order' => [
                    'po_number' => 'PO-2024-002',
                    'supplier_id' => 2,
                    'status' => 'confirmed',
                    'expected_date' => now()->addDays(5)->format('Y-m-d'),
                    'subtotal' => 675.00,
                    'total' => 675.00,
                    'notes' => 'Dye powder order. Confirmed by supplier.',
                    'created_by' => 4,
                ],
                'items' => [
                    ['raw_material_id' => 3, 'qty_ordered' => 15.0000, 'qty_received' => 0.0000, 'unit_cost' => 45.00, 'total' => 675.00],
                ],
            ],
        ];

        foreach ($orders as $data) {
            $order = PurchaseOrder::create($data['order']);

            foreach ($data['items'] as $item) {
                $order->items()->create($item);
            }
        }
    }
}
