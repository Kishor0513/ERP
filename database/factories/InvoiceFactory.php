<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\SalesOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);
        $tax = fake()->randomFloat(2, 0, 500);
        $discount = fake()->randomFloat(2, 0, 200);

        return [
            'invoice_number' => 'INV-'.fake()->unique()->bothify('######'),
            'sales_order_id' => SalesOrder::factory(),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => max(0, $subtotal + $tax - $discount),
            'currency' => 'USD',
            'due_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'status' => fake()->randomElement(['draft', 'sent', 'paid', 'partial', 'overdue', 'cancelled']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
