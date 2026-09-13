<?php

namespace Database\Factories;

use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);
        $tax = fake()->randomFloat(2, 0, 500);
        $discount = fake()->randomFloat(2, 0, 200);
        $shipping = fake()->randomFloat(2, 0, 100);

        return [
            'order_number' => 'SO-'.fake()->unique()->bothify('######'),
            'channel' => fake()->randomElement(['website', 'wholesale', 'manual', 'trade_show']),
            'status' => fake()->randomElement(['draft', 'pending_payment', 'confirmed', 'in_production', 'reserved', 'packed', 'shipped', 'delivered', 'closed', 'cancelled', 'refunded']),
            'payment_status' => fake()->randomElement(['unpaid', 'partial', 'paid', 'refunded']),
            'currency' => 'USD',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'shipping_cost' => $shipping,
            'total' => max(0, $subtotal + $tax - $discount + $shipping),
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
