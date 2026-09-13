<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteItemFactory extends Factory
{
    protected $model = QuoteItem::class;

    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 100);
        $unitPrice = fake()->randomFloat(2, 10, 1000);

        return [
            'quote_id' => Quote::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'total' => $qty * $unitPrice,
        ];
    }
}
