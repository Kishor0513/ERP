<?php

namespace Database\Factories;

use App\Models\PayrollRun;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollRunFactory extends Factory
{
    protected $model = PayrollRun::class;

    public function definition(): array
    {
        return [
            'period_start' => fake()->dateTimeBetween('-60 days', '-30 days')->format('Y-m-d'),
            'period_end' => fake()->dateTimeBetween('-29 days', 'now')->format('Y-m-d'),
            'status' => fake()->randomElement(['draft', 'calculating', 'approved', 'paid']),
            'total_amount' => fake()->randomFloat(2, 500, 20000),
            'currency' => 'USD',
        ];
    }
}
