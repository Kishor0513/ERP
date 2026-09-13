<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => fake()->unique()->bothify('VAR-######'),
            'price' => fake()->randomFloat(2, 10, 1000),
            'cost_price' => fake()->optional()->randomFloat(2, 5, 500),
            'stock_quantity' => fake()->numberBetween(0, 500),
            'weight_grams' => fake()->optional()->numberBetween(100, 5000),
            'is_active' => true,
        ];
    }
}
