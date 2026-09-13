<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'contact_name' => fake()->optional()->name(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->address(),
            'category' => fake()->randomElement(['wool', 'dye', 'packaging', 'other']),
            'lead_time_days' => fake()->optional()->numberBetween(1, 30),
            'rating' => fake()->optional()->randomFloat(2, 1, 5),
            'is_active' => true,
        ];
    }
}
