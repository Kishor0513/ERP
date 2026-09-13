<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // ── Felt Balls 2cm Solid ──
            [
                'product' => [
                    'category_id' => 2,
                    'name' => 'Felt Balls 2cm Solid',
                    'sku_prefix' => 'FB-2CM-SOLID',
                    'description' => 'Handcrafted 2cm solid felt balls made from 100% NZ wool. Perfect for crafting and decoration.',
                    'base_price' => 4.50,
                    'moq' => 1,
                    'lead_time_days' => 7,
                    'weight_grams' => 2,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'FB-2CM-SOLID-100-NW', 'price' => 4.50, 'stock_quantity' => 500, 'color_chart_entry_id' => 1, 'attribute_values' => ['2cm', '100 pack', 'Solid']],
                    ['sku' => 'FB-2CM-SOLID-100-DG', 'price' => 4.50, 'stock_quantity' => 300, 'color_chart_entry_id' => 9, 'attribute_values' => ['2cm', '100 pack', 'Solid']],
                    ['sku' => 'FB-2CM-SOLID-100-BK', 'price' => 4.50, 'stock_quantity' => 200, 'color_chart_entry_id' => 11, 'attribute_values' => ['2cm', '100 pack', 'Solid']],
                    ['sku' => 'FB-2CM-SOLID-100-RD', 'price' => 4.50, 'stock_quantity' => 150, 'color_chart_entry_id' => 12, 'attribute_values' => ['2cm', '100 pack', 'Solid']],
                ],
            ],
            // ── Felt Balls 4cm Solid ──
            [
                'product' => [
                    'category_id' => 2,
                    'name' => 'Felt Balls 4cm Solid',
                    'sku_prefix' => 'FB-4CM-SOLID',
                    'description' => 'Handcrafted 4cm solid felt balls made from 100% NZ wool.',
                    'base_price' => 6.00,
                    'moq' => 1,
                    'lead_time_days' => 7,
                    'weight_grams' => 8,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'FB-4CM-SOLID-50-NW', 'price' => 6.00, 'stock_quantity' => 250, 'color_chart_entry_id' => 1, 'attribute_values' => ['4cm', '50 pack', 'Solid']],
                    ['sku' => 'FB-4CM-SOLID-50-RD', 'price' => 6.00, 'stock_quantity' => 200, 'color_chart_entry_id' => 12, 'attribute_values' => ['4cm', '50 pack', 'Solid']],
                    ['sku' => 'FB-4CM-SOLID-50-BK', 'price' => 6.00, 'stock_quantity' => 180, 'color_chart_entry_id' => 11, 'attribute_values' => ['4cm', '50 pack', 'Solid']],
                    ['sku' => 'FB-4CM-SOLID-50-FG', 'price' => 6.00, 'stock_quantity' => 120, 'color_chart_entry_id' => 17, 'attribute_values' => ['4cm', '50 pack', 'Solid']],
                ],
            ],
            // ── Felt Balls 4cm Swirl ──
            [
                'product' => [
                    'category_id' => 3,
                    'name' => 'Felt Balls 4cm Swirl',
                    'sku_prefix' => 'FB-4CM-SWIRL',
                    'description' => 'Handcrafted 4cm swirl pattern felt balls made from 100% NZ wool.',
                    'base_price' => 7.00,
                    'moq' => 1,
                    'lead_time_days' => 7,
                    'weight_grams' => 8,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'FB-4CM-SWIRL-100-MIX', 'price' => 7.00, 'stock_quantity' => 300, 'color_chart_entry_id' => null, 'attribute_values' => ['4cm', '100 pack', 'Swirl']],
                ],
            ],
            // ── Felt Heart 3cm ──
            [
                'product' => [
                    'category_id' => 10,
                    'name' => 'Felt Heart 3cm',
                    'sku_prefix' => 'CT-3CMHEART',
                    'description' => 'Handcrafted 3cm felt heart shapes. Perfect for decoration and crafts.',
                    'base_price' => 5.00,
                    'moq' => 1,
                    'lead_time_days' => 5,
                    'weight_grams' => 3,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'CT-3CMHEART-50-RD', 'price' => 5.00, 'stock_quantity' => 400, 'color_chart_entry_id' => 12, 'attribute_values' => ['3cm', '50 pack', 'Solid']],
                    ['sku' => 'CT-3CMHEART-50-PK', 'price' => 5.00, 'stock_quantity' => 200, 'color_chart_entry_id' => 22, 'attribute_values' => ['3cm', '50 pack', 'Solid']],
                ],
            ],
            // ── Felt Star 5cm ──
            [
                'product' => [
                    'category_id' => 10,
                    'name' => 'Felt Star 5cm',
                    'sku_prefix' => 'CT-5CMSTAR',
                    'description' => 'Handcrafted 5cm felt star shapes. Ideal for holiday and festive decorations.',
                    'base_price' => 6.00,
                    'moq' => 1,
                    'lead_time_days' => 5,
                    'weight_grams' => 5,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'CT-5CMSTAR-50-GD', 'price' => 6.00, 'stock_quantity' => 250, 'color_chart_entry_id' => 15, 'attribute_values' => ['5cm', '50 pack', 'Solid']],
                ],
            ],
            // ── Cat Cave Standard ──
            [
                'product' => [
                    'category_id' => 32,
                    'name' => 'Cat Cave Standard',
                    'sku_prefix' => 'PP-CATAVE-STD',
                    'description' => 'Handmade felt cat cave in standard size. Cozy and durable for cats up to 5kg.',
                    'base_price' => 85.00,
                    'moq' => 1,
                    'lead_time_days' => 14,
                    'weight_grams' => 800,
                    'is_customizable' => true,
                    'hs_code' => '4201.00',
                ],
                'variants' => [
                    ['sku' => 'PP-CATAVE-STD-DG', 'price' => 85.00, 'cost_price' => 35.00, 'stock_quantity' => 30, 'color_chart_entry_id' => 9, 'attribute_values' => ['Standard', 'Dark Grey']],
                    ['sku' => 'PP-CATAVE-STD-BG', 'price' => 85.00, 'cost_price' => 35.00, 'stock_quantity' => 25, 'color_chart_entry_id' => 14, 'attribute_values' => ['Standard', 'Beige']],
                ],
            ],
            // ── Cat Cave Premium ──
            [
                'product' => [
                    'category_id' => 33,
                    'name' => 'Cat Cave Premium',
                    'sku_prefix' => 'PP-CATAVE-PRM',
                    'description' => 'Premium handmade felt cat cave. Thicker walls, extra insulation, for cats up to 7kg.',
                    'base_price' => 120.00,
                    'moq' => 1,
                    'lead_time_days' => 21,
                    'weight_grams' => 1200,
                    'is_customizable' => true,
                    'hs_code' => '4201.00',
                ],
                'variants' => [
                    ['sku' => 'PP-CATAVE-PRM-DG', 'price' => 120.00, 'cost_price' => 50.00, 'stock_quantity' => 20, 'color_chart_entry_id' => 9, 'attribute_values' => ['Premium', 'Dark Grey']],
                ],
            ],
            // ── Dog Bed ──
            [
                'product' => [
                    'category_id' => 34,
                    'name' => 'Felt Dog Bed',
                    'sku_prefix' => 'PP-DOGBED',
                    'description' => 'Handmade felt dog bed available in three sizes.',
                    'base_price' => 65.00,
                    'moq' => 1,
                    'lead_time_days' => 14,
                    'weight_grams' => 600,
                    'hs_code' => '4201.00',
                ],
                'variants' => [
                    ['sku' => 'PP-DOGBED-SM', 'price' => 65.00, 'cost_price' => 28.00, 'stock_quantity' => 15, 'color_chart_entry_id' => 9, 'attribute_values' => ['Small']],
                    ['sku' => 'PP-DOGBED-MD', 'price' => 85.00, 'cost_price' => 35.00, 'stock_quantity' => 12, 'color_chart_entry_id' => 9, 'attribute_values' => ['Medium']],
                    ['sku' => 'PP-DOGBED-LG', 'price' => 110.00, 'cost_price' => 45.00, 'stock_quantity' => 8, 'color_chart_entry_id' => 9, 'attribute_values' => ['Large']],
                ],
            ],
            // ── Wool Dryer Balls ──
            [
                'product' => [
                    'category_id' => 37,
                    'name' => 'Wool Dryer Balls',
                    'sku_prefix' => 'WDB',
                    'description' => '100% NZ wool dryer balls. Natural fabric softener, reusable for 1000+ loads.',
                    'base_price' => 12.00,
                    'moq' => 1,
                    'lead_time_days' => 7,
                    'weight_grams' => 120,
                    'hs_code' => '5705.00',
                ],
                'variants' => [
                    ['sku' => 'WDB-3PK', 'price' => 12.00, 'cost_price' => 4.50, 'stock_quantity' => 500, 'color_chart_entry_id' => 1, 'attribute_values' => ['3 pack']],
                    ['sku' => 'WDB-6PK', 'price' => 22.00, 'cost_price' => 8.00, 'stock_quantity' => 300, 'color_chart_entry_id' => 1, 'attribute_values' => ['6 pack']],
                ],
            ],
            // ── Felt Trivet Round ──
            [
                'product' => [
                    'category_id' => 39,
                    'name' => 'Felt Trivet Round',
                    'sku_prefix' => 'DEC-TRV-ROUND',
                    'description' => 'Handcrafted round felt trivet. Heat resistant and decorative.',
                    'base_price' => 18.00,
                    'moq' => 1,
                    'lead_time_days' => 7,
                    'weight_grams' => 150,
                    'is_customizable' => true,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'DEC-TRV-ROUND-20CM', 'price' => 18.00, 'cost_price' => 7.00, 'stock_quantity' => 100, 'color_chart_entry_id' => 1, 'attribute_values' => ['20cm']],
                    ['sku' => 'DEC-TRV-ROUND-30CM', 'price' => 25.00, 'cost_price' => 10.00, 'stock_quantity' => 80, 'color_chart_entry_id' => 1, 'attribute_values' => ['30cm']],
                ],
            ],
            // ── Felt Ball Garland 100cm ──
            [
                'product' => [
                    'category_id' => 49,
                    'name' => 'Felt Ball Garland 100cm',
                    'sku_prefix' => 'DEC-GLD-100',
                    'description' => '100cm felt ball garland with assorted colors. Perfect for room decoration.',
                    'base_price' => 15.00,
                    'moq' => 1,
                    'lead_time_days' => 5,
                    'weight_grams' => 80,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'DEC-GLD-100-PSTL', 'price' => 15.00, 'cost_price' => 6.00, 'stock_quantity' => 200, 'color_chart_entry_id' => null, 'attribute_values' => ['100cm', 'Pastel Mix']],
                    ['sku' => 'DEC-GLD-100-BOLD', 'price' => 15.00, 'cost_price' => 6.00, 'stock_quantity' => 150, 'color_chart_entry_id' => null, 'attribute_values' => ['100cm', 'Bold Mix']],
                ],
            ],
            // ── Merino Scarf ──
            [
                'product' => [
                    'category_id' => 64,
                    'name' => 'Merino Scarf',
                    'sku_prefix' => 'FSH-SCARF',
                    'description' => 'Handcrafted merino wool scarf. Soft and warm.',
                    'base_price' => 35.00,
                    'moq' => 1,
                    'lead_time_days' => 10,
                    'weight_grams' => 180,
                    'is_customizable' => true,
                    'hs_code' => '6117.10',
                ],
                'variants' => [
                    ['sku' => 'FSH-SCARF-DG', 'price' => 35.00, 'cost_price' => 14.00, 'stock_quantity' => 40, 'color_chart_entry_id' => 9, 'attribute_values' => ['Dark Grey']],
                    ['sku' => 'FSH-SCARF-CM', 'price' => 35.00, 'cost_price' => 14.00, 'stock_quantity' => 35, 'color_chart_entry_id' => 3, 'attribute_values' => ['Camel']],
                    ['sku' => 'FSH-SCARF-NW', 'price' => 35.00, 'cost_price' => 14.00, 'stock_quantity' => 30, 'color_chart_entry_id' => 1, 'attribute_values' => ['Natural White']],
                ],
            ],
            // ── Felt Tote Bag ──
            [
                'product' => [
                    'category_id' => 63,
                    'name' => 'Felt Tote Bag',
                    'sku_prefix' => 'FSH-BAG',
                    'description' => 'Handmade felt tote bag. Spacious and durable for everyday use.',
                    'base_price' => 45.00,
                    'moq' => 1,
                    'lead_time_days' => 14,
                    'weight_grams' => 300,
                    'is_customizable' => true,
                    'hs_code' => '4202.22',
                ],
                'variants' => [
                    ['sku' => 'FSH-BAG-DG', 'price' => 45.00, 'cost_price' => 18.00, 'stock_quantity' => 25, 'color_chart_entry_id' => 9, 'attribute_values' => ['Dark Grey']],
                    ['sku' => 'FSH-BAG-NW', 'price' => 45.00, 'cost_price' => 18.00, 'stock_quantity' => 20, 'color_chart_entry_id' => 1, 'attribute_values' => ['Natural White']],
                ],
            ],
            // ── Yoga Mat ──
            [
                'product' => [
                    'category_id' => 64,
                    'name' => 'Felt Yoga Mat',
                    'sku_prefix' => 'YM-183',
                    'description' => 'Handcrafted felt yoga mat, 183cm x 68cm. Non-slip natural surface.',
                    'base_price' => 95.00,
                    'moq' => 1,
                    'lead_time_days' => 14,
                    'weight_grams' => 2000,
                    'is_customizable' => true,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'YM-183-DG', 'price' => 95.00, 'cost_price' => 40.00, 'stock_quantity' => 15, 'color_chart_entry_id' => 9, 'attribute_values' => ['183cm', 'Dark Grey']],
                    ['sku' => 'YM-183-NW', 'price' => 95.00, 'cost_price' => 40.00, 'stock_quantity' => 12, 'color_chart_entry_id' => 1, 'attribute_values' => ['183cm', 'Natural White']],
                ],
            ],
            // ── Felt Ball 2cm Polka Dot ──
            [
                'product' => [
                    'category_id' => 4,
                    'name' => 'Felt Balls 2cm Polka Dot',
                    'sku_prefix' => 'FB-2CM-POLKA',
                    'description' => 'Handcrafted 2cm polka dot felt balls made from 100% NZ wool.',
                    'base_price' => 5.50,
                    'moq' => 1,
                    'lead_time_days' => 7,
                    'weight_grams' => 2,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'FB-2CM-POLKA-100', 'price' => 5.50, 'stock_quantity' => 200, 'color_chart_entry_id' => null, 'attribute_values' => ['2cm', '100 pack', 'Polka Dot']],
                ],
            ],
            // ── Felt Ball 4cm Glitter ──
            [
                'product' => [
                    'category_id' => 5,
                    'name' => 'Felt Balls 4cm Glitter',
                    'sku_prefix' => 'FB-4CM-GLIT',
                    'description' => 'Handcrafted 4cm glitter felt balls. Sparkle finish for festive crafts.',
                    'base_price' => 8.00,
                    'moq' => 1,
                    'lead_time_days' => 10,
                    'weight_grams' => 8,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'FB-4CM-GLIT-100', 'price' => 8.00, 'stock_quantity' => 150, 'color_chart_entry_id' => null, 'attribute_values' => ['4cm', '100 pack', 'Glitter']],
                ],
            ],
            // ── Felt Ball 4cm Tie-Dye ──
            [
                'product' => [
                    'category_id' => 6,
                    'name' => 'Felt Balls 4cm Tie-Dye',
                    'sku_prefix' => 'FB-4CM-TIE',
                    'description' => 'Handcrafted 4cm tie-dye felt balls. Unique color patterns.',
                    'base_price' => 8.00,
                    'moq' => 1,
                    'lead_time_days' => 10,
                    'weight_grams' => 8,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'FB-4CM-TIE-100', 'price' => 8.00, 'stock_quantity' => 120, 'color_chart_entry_id' => null, 'attribute_values' => ['4cm', '100 pack', 'Tie-Dye']],
                ],
            ],
            // ── Felt Pumpkin (Seasonal) ──
            [
                'product' => [
                    'category_id' => 19,
                    'name' => 'Felt Pumpkin',
                    'sku_prefix' => 'SEAS-PUMP',
                    'description' => 'Handcrafted felt pumpkin decoration. Perfect for fall and Halloween.',
                    'base_price' => 10.00,
                    'moq' => 1,
                    'lead_time_days' => 7,
                    'weight_grams' => 100,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'SEAS-PUMP-SM', 'price' => 10.00, 'cost_price' => 4.00, 'stock_quantity' => 100, 'color_chart_entry_id' => 14, 'attribute_values' => ['Small', 'Orange']],
                    ['sku' => 'SEAS-PUMP-LG', 'price' => 16.00, 'cost_price' => 6.50, 'stock_quantity' => 80, 'color_chart_entry_id' => 14, 'attribute_values' => ['Large', 'Orange']],
                ],
            ],
            // ── Felt Ball 6cm Solid ──
            [
                'product' => [
                    'category_id' => 2,
                    'name' => 'Felt Balls 6cm Solid',
                    'sku_prefix' => 'FB-6CM-SOLID',
                    'description' => 'Handcrafted 6cm solid felt balls made from 100% NZ wool.',
                    'base_price' => 9.00,
                    'moq' => 1,
                    'lead_time_days' => 7,
                    'weight_grams' => 20,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'FB-6CM-SOLID-50-NW', 'price' => 9.00, 'stock_quantity' => 150, 'color_chart_entry_id' => 1, 'attribute_values' => ['6cm', '50 pack', 'Solid']],
                    ['sku' => 'FB-6CM-SOLID-50-DG', 'price' => 9.00, 'stock_quantity' => 120, 'color_chart_entry_id' => 9, 'attribute_values' => ['6cm', '50 pack', 'Solid']],
                ],
            ],
            // ── Recycled Silk Yarn ──
            [
                'product' => [
                    'category_id' => 58,
                    'name' => 'Recycled Silk Yarn',
                    'sku_prefix' => 'YRN-SILK-100',
                    'description' => '100g skein of recycled silk yarn. Eco-friendly and vibrant.',
                    'base_price' => 12.00,
                    'moq' => 1,
                    'lead_time_days' => 10,
                    'weight_grams' => 100,
                    'hs_code' => '5208.90',
                ],
                'variants' => [
                    ['sku' => 'YRN-SILK-100-MIX', 'price' => 12.00, 'cost_price' => 5.00, 'stock_quantity' => 200, 'color_chart_entry_id' => null, 'attribute_values' => ['100g', 'Mixed']],
                ],
            ],
            // ── Banana Fiber Yarn ──
            [
                'product' => [
                    'category_id' => 57,
                    'name' => 'Banana Fiber Yarn',
                    'sku_prefix' => 'YRN-BNNA-100',
                    'description' => '100g skein of banana fiber yarn. Natural and biodegradable.',
                    'base_price' => 10.00,
                    'moq' => 1,
                    'lead_time_days' => 10,
                    'weight_grams' => 100,
                    'hs_code' => '5308.90',
                ],
                'variants' => [
                    ['sku' => 'YRN-BNNA-100-NAT', 'price' => 10.00, 'cost_price' => 4.00, 'stock_quantity' => 180, 'color_chart_entry_id' => 1, 'attribute_values' => ['100g', 'Natural']],
                ],
            ],
            // ── Merino Wool Yarn ──
            [
                'product' => [
                    'category_id' => 60,
                    'name' => 'Merino Wool Yarn',
                    'sku_prefix' => 'YRN-MRNO-100',
                    'description' => '100g skein of premium merino wool yarn. Ultra-soft and warm.',
                    'base_price' => 15.00,
                    'moq' => 1,
                    'lead_time_days' => 10,
                    'weight_grams' => 100,
                    'hs_code' => '5108.00',
                ],
                'variants' => [
                    ['sku' => 'YRN-MRNO-100-NW', 'price' => 15.00, 'cost_price' => 6.00, 'stock_quantity' => 150, 'color_chart_entry_id' => 1, 'attribute_values' => ['100g', 'Natural White']],
                    ['sku' => 'YRN-MRNO-100-DG', 'price' => 15.00, 'cost_price' => 6.50, 'stock_quantity' => 120, 'color_chart_entry_id' => 9, 'attribute_values' => ['100g', 'Dark Grey']],
                ],
            ],
            // ── Felt Sheet A4 ──
            [
                'product' => [
                    'category_id' => 15,
                    'name' => 'Felt Sheet A4',
                    'sku_prefix' => 'CS-SHEET-A4',
                    'description' => 'A4 size felt sheet (210mm x 297mm), 1.5mm thick. Ideal for crafts and DIY.',
                    'base_price' => 2.00,
                    'moq' => 10,
                    'lead_time_days' => 5,
                    'weight_grams' => 15,
                    'hs_code' => '5602.10',
                ],
                'variants' => [
                    ['sku' => 'CS-SHEET-A4-NW', 'price' => 2.00, 'cost_price' => 0.80, 'stock_quantity' => 1000, 'color_chart_entry_id' => 1, 'attribute_values' => ['A4', 'Natural White']],
                    ['sku' => 'CS-SHEET-A4-RD', 'price' => 2.00, 'cost_price' => 0.80, 'stock_quantity' => 500, 'color_chart_entry_id' => 12, 'attribute_values' => ['A4', 'Red']],
                    ['sku' => 'CS-SHEET-A4-FG', 'price' => 2.00, 'cost_price' => 0.80, 'stock_quantity' => 400, 'color_chart_entry_id' => 17, 'attribute_values' => ['A4', 'Forest Green']],
                ],
            ],
            // ── Felt Ball 3cm Stone Shape ──
            [
                'product' => [
                    'category_id' => 8,
                    'name' => 'Felt Ball 3cm Stone Shape',
                    'sku_prefix' => 'FB-3CM-STONE',
                    'description' => 'Handcrafted 3cm stone-shaped felt balls. Organic, irregular shapes.',
                    'base_price' => 5.00,
                    'moq' => 1,
                    'lead_time_days' => 7,
                    'weight_grams' => 4,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'FB-3CM-STONE-100-MIX', 'price' => 5.00, 'stock_quantity' => 200, 'color_chart_entry_id' => null, 'attribute_values' => ['3cm', '100 pack', 'Stone Shape']],
                ],
            ],
            // ── Felt Oval 4cm ──
            [
                'product' => [
                    'category_id' => 7,
                    'name' => 'Felt Oval 4cm',
                    'sku_prefix' => 'FB-4CM-OVAL',
                    'description' => 'Handcrafted 4cm oval felt pieces. Versatile for crafts and decoration.',
                    'base_price' => 6.00,
                    'moq' => 1,
                    'lead_time_days' => 7,
                    'weight_grams' => 7,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'FB-4CM-OVAL-50-MIX', 'price' => 6.00, 'stock_quantity' => 180, 'color_chart_entry_id' => null, 'attribute_values' => ['4cm', '50 pack', 'Oval']],
                ],
            ],
            // ── Wicker Basket Small ──
            [
                'product' => [
                    'category_id' => 43,
                    'name' => 'Wicker Basket Small',
                    'sku_prefix' => 'DEC-BASK-SM',
                    'description' => 'Small handwoven wicker basket. Perfect for storage and display.',
                    'base_price' => 22.00,
                    'moq' => 1,
                    'lead_time_days' => 14,
                    'weight_grams' => 400,
                    'hs_code' => '4602.19',
                ],
                'variants' => [
                    ['sku' => 'DEC-BASK-SM-NAT', 'price' => 22.00, 'cost_price' => 9.00, 'stock_quantity' => 50, 'color_chart_entry_id' => 1, 'attribute_values' => ['Small', 'Natural']],
                ],
            ],
            // ── Felt Cushion Round ──
            [
                'product' => [
                    'category_id' => 45,
                    'name' => 'Felt Cushion Round',
                    'sku_prefix' => 'DEC-CUSH-RND',
                    'description' => 'Handcrafted round felt cushion. Comfortable and decorative.',
                    'base_price' => 55.00,
                    'moq' => 1,
                    'lead_time_days' => 14,
                    'weight_grams' => 500,
                    'is_customizable' => true,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'DEC-CUSH-RND-35CM', 'price' => 55.00, 'cost_price' => 22.00, 'stock_quantity' => 25, 'color_chart_entry_id' => 9, 'attribute_values' => ['35cm', 'Dark Grey']],
                    ['sku' => 'DEC-CUSH-RND-45CM', 'price' => 75.00, 'cost_price' => 30.00, 'stock_quantity' => 15, 'color_chart_entry_id' => 1, 'attribute_values' => ['45cm', 'Natural White']],
                ],
            ],
            // ── Cat Mat ──
            [
                'product' => [
                    'category_id' => 36,
                    'name' => 'Cat Mat',
                    'sku_prefix' => 'PP-CATMAT',
                    'description' => 'Handcrafted felt cat mat. Soft and washable.',
                    'base_price' => 30.00,
                    'moq' => 1,
                    'lead_time_days' => 7,
                    'weight_grams' => 300,
                    'hs_code' => '4201.00',
                ],
                'variants' => [
                    ['sku' => 'PP-CATMAT-DG', 'price' => 30.00, 'cost_price' => 12.00, 'stock_quantity' => 40, 'color_chart_entry_id' => 9, 'attribute_values' => ['Dark Grey']],
                    ['sku' => 'PP-CATMAT-NW', 'price' => 30.00, 'cost_price' => 12.00, 'stock_quantity' => 35, 'color_chart_entry_id' => 1, 'attribute_values' => ['Natural White']],
                ],
            ],
            // ── Felt Wreath 30cm ──
            [
                'product' => [
                    'category_id' => 48,
                    'name' => 'Felt Wreath 30cm',
                    'sku_prefix' => 'DEC-WREATH',
                    'description' => 'Handcrafted 30cm felt wreath. Beautiful for doors and walls.',
                    'base_price' => 40.00,
                    'moq' => 1,
                    'lead_time_days' => 10,
                    'weight_grams' => 250,
                    'is_customizable' => true,
                    'hs_code' => '5703.90',
                ],
                'variants' => [
                    ['sku' => 'DEC-WREATH-30-FG', 'price' => 40.00, 'cost_price' => 16.00, 'stock_quantity' => 30, 'color_chart_entry_id' => 17, 'attribute_values' => ['30cm', 'Forest Green']],
                    ['sku' => 'DEC-WREATH-30-PK', 'price' => 40.00, 'cost_price' => 16.00, 'stock_quantity' => 25, 'color_chart_entry_id' => 22, 'attribute_values' => ['30cm', 'Pink']],
                ],
            ],
            // ── Pet Toy Ball Set ──
            [
                'product' => [
                    'category_id' => 35,
                    'name' => 'Pet Toy Ball Set',
                    'sku_prefix' => 'PP-TOYBALL',
                    'description' => 'Set of 3 felted wool pet toy balls. Safe and durable for dogs and cats.',
                    'base_price' => 18.00,
                    'moq' => 1,
                    'lead_time_days' => 7,
                    'weight_grams' => 100,
                    'hs_code' => '4201.00',
                ],
                'variants' => [
                    ['sku' => 'PP-TOYBALL-SET3', 'price' => 18.00, 'cost_price' => 7.00, 'stock_quantity' => 200, 'color_chart_entry_id' => null, 'attribute_values' => ['3 pack', 'Assorted']],
                ],
            ],
        ];

        foreach ($products as $data) {
            $product = Product::create($data['product']);

            foreach ($data['variants'] as $variant) {
                $product->variants()->create($variant);
            }
        }
    }
}
