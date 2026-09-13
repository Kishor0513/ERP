<?php

namespace Database\Factories;

use App\Models\RawMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;

class RawMaterialFactory extends Factory
{
    protected $model = RawMaterial::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'sku' => fake()->unique()->bothify('RM-######'),
            'unit' => fake()->randomElement(['kg', 'grams', 'meters', 'pieces', 'liters']),
            'description' => fake()->optional()->sentence(),
            'reorder_point' => fake()->numberBetween(0, 100),
            'current_stock' => fake()->randomFloat(4, 0, 1000),
            'cost_per_unit' => fake()->randomFloat(2, 1, 500),
        ];
    }
}
