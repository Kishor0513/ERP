<?php

namespace Database\Factories;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);
        $tax = fake()->randomFloat(2, 0, 500);
        $discount = fake()->randomFloat(2, 0, 200);

        return [
            'quote_number' => 'Q-'.fake()->unique()->bothify('######'),
            'status' => fake()->randomElement(['draft', 'sent', 'accepted', 'rejected', 'expired']),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => max(0, $subtotal + $tax - $discount),
            'currency' => 'USD',
            'valid_until' => fake()->optional()->dateTimeBetween('now', '+30 days')?->format('Y-m-d'),
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
