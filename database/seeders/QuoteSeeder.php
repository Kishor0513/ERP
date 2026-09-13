<?php

namespace Database\Seeders;

use App\Models\Quote;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        $quotes = [
            [
                'quote' => [
                    'quote_number' => 'QUO-2024-001',
                    'wholesale_account_id' => 1,
                    'lead_id' => null,
                    'status' => 'draft',
                    'subtotal' => 2450.00,
                    'tax' => 0.00,
                    'discount' => 0.00,
                    'total' => 2450.00,
                    'currency' => 'USD',
                    'valid_until' => now()->addDays(30)->format('Y-m-d'),
                    'notes' => 'Initial quote for Woolly Friends Pet Co. Cat caves and dog beds.',
                    'created_by' => 3,
                ],
                'items' => [
                    ['product_variant_id' => null, 'custom_description' => 'Cat Cave Standard - Dark Grey (Bulk)', 'qty' => 20, 'unit_price' => 75.00, 'total' => 1500.00],
                    ['product_variant_id' => null, 'custom_description' => 'Felt Dog Bed - Medium (Bulk)', 'qty' => 10, 'unit_price' => 75.00, 'total' => 750.00],
                    ['product_variant_id' => null, 'custom_description' => 'Pet Toy Ball Set - Assorted', 'qty' => 15, 'unit_price' => 13.33, 'total' => 200.00],
                ],
            ],
            [
                'quote' => [
                    'quote_number' => 'QUO-2024-002',
                    'wholesale_account_id' => 2,
                    'lead_id' => null,
                    'status' => 'sent',
                    'subtotal' => 8750.00,
                    'tax' => 0.00,
                    'discount' => 0.00,
                    'total' => 8750.00,
                    'currency' => 'USD',
                    'valid_until' => now()->addDays(30)->format('Y-m-d'),
                    'notes' => 'Bulk felt balls and craft supplies for Craft Haven Boutique.',
                    'created_by' => 3,
                ],
                'items' => [
                    ['product_variant_id' => null, 'custom_description' => 'Felt Balls 2cm Solid - Natural White (100 pack)', 'qty' => 50, 'unit_price' => 40.00, 'total' => 2000.00],
                    ['product_variant_id' => null, 'custom_description' => 'Felt Balls 4cm Solid - Assorted (50 pack)', 'qty' => 40, 'unit_price' => 50.00, 'total' => 2000.00],
                    ['product_variant_id' => null, 'custom_description' => 'Felt Sheets A4 - Mixed Colors', 'qty' => 500, 'unit_price' => 1.75, 'total' => 875.00],
                    ['product_variant_id' => null, 'custom_description' => 'Felt Heart 3cm - Red (50 pack)', 'qty' => 30, 'unit_price' => 4.50, 'total' => 135.00],
                    ['product_variant_id' => null, 'custom_description' => 'Felt Ball Garland 100cm - Pastel', 'qty' => 50, 'unit_price' => 12.00, 'total' => 600.00],
                ],
            ],
            [
                'quote' => [
                    'quote_number' => 'QUO-2024-003',
                    'wholesale_account_id' => 3,
                    'lead_id' => null,
                    'status' => 'accepted',
                    'subtotal' => 15200.00,
                    'tax' => 0.00,
                    'discount' => 0.00,
                    'total' => 15200.00,
                    'currency' => 'USD',
                    'valid_until' => now()->addDays(60)->format('Y-m-d'),
                    'notes' => 'Large order for Nordic Home Decor. Premium products.',
                    'created_by' => 3,
                ],
                'items' => [
                    ['product_variant_id' => null, 'custom_description' => 'Cat Cave Premium - Dark Grey', 'qty' => 50, 'unit_price' => 100.00, 'total' => 5000.00],
                    ['product_variant_id' => null, 'custom_description' => 'Felt Wreath 30cm - Forest Green', 'qty' => 30, 'unit_price' => 35.00, 'total' => 1050.00],
                    ['product_variant_id' => null, 'custom_description' => 'Felt Cushion Round 45cm - Natural White', 'qty' => 25, 'unit_price' => 65.00, 'total' => 1625.00],
                    ['product_variant_id' => null, 'custom_description' => 'Felt Trivet Round 30cm', 'qty' => 50, 'unit_price' => 22.00, 'total' => 1100.00],
                    ['product_variant_id' => null, 'custom_description' => 'Wool Dryer Balls 6-pack', 'qty' => 100, 'unit_price' => 18.00, 'total' => 1800.00],
                    ['product_variant_id' => null, 'custom_description' => 'Felt Ball Garland 100cm - Bold Mix', 'qty' => 150, 'unit_price' => 12.00, 'total' => 1800.00],
                    ['product_variant_id' => null, 'custom_description' => 'Merino Scarf - Assorted Colors', 'qty' => 40, 'unit_price' => 30.00, 'total' => 1200.00],
                    ['product_variant_id' => null, 'custom_description' => 'Wicker Basket Small', 'qty' => 30, 'unit_price' => 19.00, 'total' => 570.00],
                    ['product_variant_id' => null, 'custom_description' => 'Felt Tote Bag - Dark Grey', 'qty' => 20, 'unit_price' => 40.00, 'total' => 800.00],
                    ['product_variant_id' => null, 'custom_description' => 'Cat Mat - Dark Grey', 'qty' => 25, 'unit_price' => 25.00, 'total' => 255.00],
                ],
            ],
        ];

        foreach ($quotes as $data) {
            $quote = Quote::create($data['quote']);

            foreach ($data['items'] as $item) {
                $quote->items()->create($item);
            }
        }
    }
}
