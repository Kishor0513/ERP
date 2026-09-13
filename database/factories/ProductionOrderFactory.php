<?php

namespace Database\Factories;

use App\Models\ProductionOrder;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionOrderFactory extends Factory
{
    protected $model = ProductionOrder::class;

    public function definition(): array
    {
        return [
            'production_order_number' => 'PO-'.fake()->unique()->bothify('######'),
            'product_variant_id' => ProductVariant::factory(),
            'qty_ordered' => fake()->numberBetween(1, 500),
            'qty_completed' => 0,
            'status' => fake()->randomElement(['pending', 'materials_ready', 'in_progress', 'submitted_qc', 'qc_passed', 'qc_failed', 'rework', 'completed', 'cancelled']),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+60 days')?->format('Y-m-d'),
            'is_custom' => false,
        ];
    }
}
