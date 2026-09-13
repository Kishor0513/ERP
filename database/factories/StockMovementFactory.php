<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'item_type' => 'product_variant',
            'item_id' => ProductVariant::factory(),
            'warehouse_id' => Warehouse::factory(),
            'type' => fake()->randomElement(['receipt', 'consumption', 'output', 'adjustment', 'transfer', 'dispatch', 'return']),
            'qty' => fake()->randomFloat(4, 1, 100),
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
