<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Main Warehouse',
                'type' => 'main',
                'address' => 'Kathmandu Industrial District, Ward 14, Kathmandu 44600, Nepal',
                'is_active' => true,
            ],
            [
                'name' => 'Bhaktapur Artisan Center',
                'type' => 'artisan_center',
                'address' => 'Bhaktapur Artisan Zone, Ward 8, Bhaktapur 44800, Nepal',
                'is_active' => true,
            ],
            [
                'name' => 'Export Staging Hub',
                'type' => 'export_staging',
                'address' => 'Tribhuvan International Airport Cargo Area, Kathmandu 44600, Nepal',
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }
}
