<?php

namespace Database\Seeders;

use App\Models\PieceRate;
use Illuminate\Database\Seeder;

class PieceRateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            [
                'product_id' => null,
                'operation' => 'felting',
                'rate' => 0.0500,
                'currency' => 'USD',
                'effective_from' => '2024-01-01',
                'effective_to' => null,
            ],
            [
                'product_id' => null,
                'operation' => 'needle_felting',
                'rate' => 0.0800,
                'currency' => 'USD',
                'effective_from' => '2024-01-01',
                'effective_to' => null,
            ],
            [
                'product_id' => null,
                'operation' => 'dyeing',
                'rate' => 0.0300,
                'currency' => 'USD',
                'effective_from' => '2024-01-01',
                'effective_to' => null,
            ],
            [
                'product_id' => null,
                'operation' => 'stitching',
                'rate' => 0.2000,
                'currency' => 'USD',
                'effective_from' => '2024-01-01',
                'effective_to' => null,
            ],
            [
                'product_id' => null,
                'operation' => 'assembly',
                'rate' => 0.2500,
                'currency' => 'USD',
                'effective_from' => '2024-01-01',
                'effective_to' => null,
            ],
        ];

        foreach ($rates as $rate) {
            PieceRate::create($rate);
        }
    }
}
