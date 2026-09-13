<?php

namespace Database\Seeders;

use App\Models\Shipment;
use Illuminate\Database\Seeder;

class ShipmentSeeder extends Seeder
{
    public function run(): void
    {
        $shipments = [
            [
                'shipment_number' => 'SHP-2024-001',
                'sales_order_id' => 1,
                'carrier' => 'DHL',
                'tracking_no' => 'DH1234567890',
                'incoterm' => 'FOB',
                'hs_code' => '5703.90',
                'declared_value' => 487.50,
                'currency' => 'USD',
                'status' => 'delivered',
                'estimated_arrival' => now()->subDays(3)->toDateTimeString(),
                'actual_arrival' => now()->subDays(2)->toDateTimeString(),
                'certificate_of_origin' => 'Nepal COO-2024-001',
                'fair_trade_doc' => 'FT-2024-001',
                'notes' => 'Delivered successfully.',
                'created_by' => 8,
            ],
            [
                'shipment_number' => 'SHP-2024-002',
                'sales_order_id' => 3,
                'carrier' => 'FedEx',
                'tracking_no' => null,
                'incoterm' => 'CIF',
                'hs_code' => '4201.00',
                'declared_value' => 8100.00,
                'currency' => 'USD',
                'status' => 'preparing',
                'estimated_arrival' => now()->addDays(14)->toDateTimeString(),
                'actual_arrival' => null,
                'certificate_of_origin' => null,
                'fair_trade_doc' => null,
                'notes' => 'Large order. Awaiting customs documentation.',
                'created_by' => 8,
            ],
        ];

        foreach ($shipments as $shipment) {
            Shipment::create($shipment);
        }
    }
}
