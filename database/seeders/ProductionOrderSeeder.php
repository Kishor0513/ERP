<?php

namespace Database\Seeders;

use App\Models\ProductionOrder;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductionOrderSeeder extends Seeder
{
    public function run(): void
    {
        $variants = ProductVariant::pluck('id')->toArray();

        $orders = [
            [
                'order' => [
                    'production_order_number' => 'PO-2024-001',
                    'sales_order_id' => 2,
                    'product_variant_id' => $variants[0] ?? 1,
                    'qty_ordered' => 500,
                    'qty_completed' => 0,
                    'status' => 'in_progress',
                    'due_date' => now()->addDays(14)->format('Y-m-d'),
                    'is_custom' => false,
                ],
                'assignments' => [
                    ['artisan_id' => 1, 'qty_assigned' => 250, 'qty_completed' => 0, 'status' => 'in_progress', 'assigned_at' => now()->subDays(3)],
                    ['artisan_id' => 2, 'qty_assigned' => 250, 'qty_completed' => 0, 'status' => 'assigned', 'assigned_at' => now()->subDays(2)],
                ],
            ],
            [
                'order' => [
                    'production_order_number' => 'PO-2024-002',
                    'sales_order_id' => 3,
                    'product_variant_id' => $variants[12] ?? 13,
                    'qty_ordered' => 200,
                    'qty_completed' => 0,
                    'status' => 'materials_ready',
                    'due_date' => now()->addDays(21)->format('Y-m-d'),
                    'is_custom' => false,
                ],
                'assignments' => [
                    ['artisan_id' => 5, 'qty_assigned' => 100, 'qty_completed' => 0, 'status' => 'assigned', 'assigned_at' => now()->subDays(1)],
                ],
            ],
            [
                'order' => [
                    'production_order_number' => 'PO-2024-003',
                    'sales_order_id' => 1,
                    'product_variant_id' => $variants[8] ?? 9,
                    'qty_ordered' => 100,
                    'qty_completed' => 100,
                    'status' => 'completed',
                    'due_date' => now()->subDays(5)->format('Y-m-d'),
                    'is_custom' => false,
                ],
                'assignments' => [
                    ['artisan_id' => 3, 'qty_assigned' => 50, 'qty_completed' => 50, 'status' => 'completed', 'assigned_at' => now()->subDays(10), 'completed_at' => now()->subDays(5)],
                    ['artisan_id' => 4, 'qty_assigned' => 50, 'qty_completed' => 50, 'status' => 'completed', 'assigned_at' => now()->subDays(10), 'completed_at' => now()->subDays(5)],
                ],
            ],
        ];

        foreach ($orders as $data) {
            $order = ProductionOrder::create($data['order']);

            foreach ($data['assignments'] as $assignment) {
                $order->assignments()->create($assignment);
            }
        }
    }
}
