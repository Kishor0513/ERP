<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 10000);

        return [
            'po_number' => 'PO-'.fake()->unique()->bothify('######'),
            'supplier_id' => Supplier::factory(),
            'status' => fake()->randomElement(['draft', 'submitted', 'confirmed', 'partially_received', 'received', 'cancelled']),
            'expected_date' => fake()->optional()->dateTimeBetween('now', '+60 days')?->format('Y-m-d'),
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
