<?php

namespace Database\Seeders;

use App\Models\RawMaterial;
use Illuminate\Database\Seeder;

class RawMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            [
                'name' => 'NZ Wool Roving Natural White',
                'sku' => 'RM-WOOL-NW',
                'unit' => 'kg',
                'description' => 'Premium New Zealand wool roving in natural white. Ideal for felting.',
                'reorder_point' => 50,
                'current_stock' => 200.0000,
                'cost_per_unit' => 12.50,
            ],
            [
                'name' => 'NZ Wool Roving Dyed Assorted',
                'sku' => 'RM-WOOL-DYED',
                'unit' => 'kg',
                'description' => 'Pre-dyed NZ wool roving in assorted colors.',
                'reorder_point' => 30,
                'current_stock' => 150.0000,
                'cost_per_unit' => 15.00,
            ],
            [
                'name' => 'Dye Powder Assorted',
                'sku' => 'RM-DYE-ASP',
                'unit' => 'kg',
                'description' => 'Assorted dye powders for wool and fiber dyeing.',
                'reorder_point' => 10,
                'current_stock' => 25.0000,
                'cost_per_unit' => 45.00,
            ],
            [
                'name' => 'Thread Spool',
                'sku' => 'RM-THREAD-SP',
                'unit' => 'pieces',
                'description' => 'Strong polyester thread spools for stitching felt products.',
                'reorder_point' => 100,
                'current_stock' => 500.0000,
                'cost_per_unit' => 2.00,
            ],
            [
                'name' => 'Packaging Box Medium',
                'sku' => 'RM-PKG-BOX-M',
                'unit' => 'pieces',
                'description' => 'Medium corrugated packaging boxes (30cm x 20cm x 15cm).',
                'reorder_point' => 200,
                'current_stock' => 1000.0000,
                'cost_per_unit' => 0.75,
            ],
            [
                'name' => 'Tissue Paper Pack',
                'sku' => 'RM-PKG-TISSUE',
                'unit' => 'pieces',
                'description' => 'Acid-free tissue paper sheets for product wrapping.',
                'reorder_point' => 500,
                'current_stock' => 2000.0000,
                'cost_per_unit' => 0.10,
            ],
            [
                'name' => 'Shipping Label Roll',
                'sku' => 'RM-PKG-LABEL',
                'unit' => 'pieces',
                'description' => 'Thermal shipping label rolls for packaging.',
                'reorder_point' => 100,
                'current_stock' => 500.0000,
                'cost_per_unit' => 0.05,
            ],
            [
                'name' => 'Needle Felting Needles Pack',
                'sku' => 'RM-TOOL-NEEDLE',
                'unit' => 'pieces',
                'description' => 'Pack of 10 assorted gauge felting needles.',
                'reorder_point' => 20,
                'current_stock' => 100.0000,
                'cost_per_unit' => 8.00,
            ],
        ];

        foreach ($materials as $material) {
            RawMaterial::create($material);
        }
    }
}
