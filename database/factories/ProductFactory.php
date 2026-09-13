<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'sku_prefix' => strtoupper(Str::random(4)),
            'description' => fake()->sentence(),
            'base_price' => fake()->randomFloat(2, 10, 1000),
            'is_customizable' => fake()->boolean(20),
            'is_active' => true,
            'moq' => fake()->numberBetween(1, 50),
            'lead_time_days' => fake()->optional()->numberBetween(1, 30),
            'weight_grams' => fake()->optional()->numberBetween(100, 5000),
        ];
    }
}
