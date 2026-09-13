<?php

namespace Database\Factories;

use App\Models\WholesaleAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class WholesaleAccountFactory extends Factory
{
    protected $model = WholesaleAccount::class;

    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'contact_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->streetAddress(),
            'city' => fake()->optional()->city(),
            'country' => fake()->optional()->country(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected', 'suspended']),
            'payment_terms' => fake()->optional()->randomElement(['net_30', 'net_60', 'deposit_only']),
            'credit_limit' => fake()->optional()->randomFloat(2, 1000, 50000),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
