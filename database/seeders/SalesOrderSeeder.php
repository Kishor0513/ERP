<?php

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\SalesOrder;
use Illuminate\Database\Seeder;

class SalesOrderSeeder extends Seeder
{
    public function run(): void
    {
        $variants = ProductVariant::pluck('id')->toArray();

        $orders = [
            [
                'order' => [
                    'order_number' => 'SO-2024-001',
                    'channel' => 'website',
                    'wholesale_account_id' => null,
                    'lead_id' => null,
                    'status' => 'shipped',
                    'payment_status' => 'paid',
                    'currency' => 'USD',
                    'subtotal' => 450.00,
                    'tax' => 0.00,
                    'discount' => 0.00,
                    'shipping_cost' => 37.50,
                    'total' => 487.50,
                    'po_number' => null,
                    'payment_terms' => null,
                    'notes' => 'Website order. Express shipping.',
                    'created_by' => 3,
                ],
                'items' => [
                    ['product_variant_id' => $variants[0] ?? 1, 'qty' => 5, 'unit_price' => 45.00, 'total' => 225.00],
                    ['product_variant_id' => $variants[1] ?? 2, 'qty' => 3, 'unit_price' => 75.00, 'total' => 225.00],
                ],
            ],
            [
                'order' => [
                    'order_number' => 'SO-2024-002',
                    'channel' => 'wholesale',
                    'wholesale_account_id' => 2,
                    'lead_id' => null,
                    'status' => 'in_production',
                    'payment_status' => 'partial',
                    'currency' => 'USD',
                    'subtotal' => 4375.00,
                    'tax' => 0.00,
                    'discount' => 0.00,
                    'shipping_cost' => 0.00,
                    'total' => 4375.00,
                    'po_number' => 'CH-PO-2024-001',
                    'payment_terms' => 'net_30',
                    'notes' => 'Wholesale order from Craft Haven Boutique. 50% deposit received.',
                    'created_by' => 3,
                ],
                'items' => [
                    ['product_variant_id' => $variants[0] ?? 1, 'qty' => 50, 'unit_price' => 40.00, 'total' => 2000.00],
                    ['product_variant_id' => $variants[5] ?? 6, 'qty' => 25, 'unit_price' => 50.00, 'total' => 1250.00],
                    ['product_variant_id' => $variants[10] ?? 11, 'qty' => 50, 'unit_price' => 22.50, 'total' => 1125.00],
                ],
            ],
            [
                'order' => [
                    'order_number' => 'SO-2024-003',
                    'channel' => 'wholesale',
                    'wholesale_account_id' => 3,
                    'lead_id' => null,
                    'status' => 'packed',
                    'payment_status' => 'paid',
                    'currency' => 'USD',
                    'subtotal' => 8100.00,
                    'tax' => 0.00,
                    'discount' => 0.00,
                    'shipping_cost' => 0.00,
                    'total' => 8100.00,
                    'po_number' => 'NH-PO-2024-001',
                    'payment_terms' => 'net_60',
                    'notes' => 'Large wholesale order. Fully paid.',
                    'created_by' => 3,
                ],
                'items' => [
                    ['product_variant_id' => $variants[12] ?? 13, 'qty' => 50, 'unit_price' => 100.00, 'total' => 5000.00],
                    ['product_variant_id' => $variants[15] ?? 16, 'qty' => 30, 'unit_price' => 35.00, 'total' => 1050.00],
                    ['product_variant_id' => $variants[20] ?? 21, 'qty' => 40, 'unit_price' => 30.00, 'total' => 1200.00],
                    ['product_variant_id' => $variants[25] ?? 26, 'qty' => 30, 'unit_price' => 28.33, 'total' => 850.00],
                ],
            ],
            [
                'order' => [
                    'order_number' => 'SO-2024-004',
                    'channel' => 'manual',
                    'wholesale_account_id' => null,
                    'lead_id' => 3,
                    'status' => 'draft',
                    'payment_status' => 'unpaid',
                    'currency' => 'USD',
                    'subtotal' => 1250.00,
                    'tax' => 0.00,
                    'discount' => 0.00,
                    'shipping_cost' => 0.00,
                    'total' => 1250.00,
                    'po_number' => null,
                    'payment_terms' => null,
                    'notes' => 'Manual order draft. Pending approval.',
                    'created_by' => 3,
                ],
                'items' => [
                    ['product_variant_id' => $variants[12] ?? 13, 'qty' => 10, 'unit_price' => 85.00, 'total' => 850.00],
                    ['product_variant_id' => $variants[15] ?? 16, 'qty' => 5, 'unit_price' => 80.00, 'total' => 400.00],
                ],
            ],
        ];

        foreach ($orders as $data) {
            $order = SalesOrder::create($data['order']);

            foreach ($data['items'] as $item) {
                $order->items()->create($item);
            }
        }
    }
}
