<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true).' Warehouse',
            'type' => fake()->randomElement(['main', 'artisan_center', 'export_staging']),
            'address' => fake()->optional()->address(),
            'is_active' => true,
        ];
    }
}
