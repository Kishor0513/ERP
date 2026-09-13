<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Canterbury Wool Traders',
                'contact_name' => 'James Morrison',
                'email' => 'james@canterburywool.co.nz',
                'phone' => '+64-21-555-0101',
                'address' => '123 Cashmere Road, Christchurch 8022, New Zealand',
                'category' => 'wool',
                'lead_time_days' => 14,
                'rating' => 4.80,
            ],
            [
                'name' => 'Kathmandu Dye Works',
                'contact_name' => 'Prakash Shrestha',
                'email' => 'prakash@kathmandudyeworks.com.np',
                'phone' => '+977-1-4445678',
                'address' => 'Thamel Marg, Kathmandu 44600, Nepal',
                'category' => 'dye',
                'lead_time_days' => 3,
                'rating' => 4.50,
            ],
            [
                'name' => 'Valley Packaging',
                'contact_name' => 'Raj Kumar Thapa',
                'email' => 'raj@valleypackaging.com.np',
                'phone' => '+977-1-5551234',
                'address' => 'Bhaktapur Industrial District, Bhaktapur 44800, Nepal',
                'category' => 'packaging',
                'lead_time_days' => 2,
                'rating' => 4.20,
            ],
            [
                'name' => 'Himalayan Thread Co',
                'contact_name' => 'Suman Lama',
                'email' => 'suman@himalayanthread.com.np',
                'phone' => '+977-1-6667890',
                'address' => 'Lalitpur Industrial Estate, Lalitpur 44700, Nepal',
                'category' => 'other',
                'lead_time_days' => 5,
                'rating' => 4.30,
            ],
            [
                'name' => 'Artisan Tools Nepal',
                'contact_name' => 'Binod Maharjan',
                'email' => 'binod@artisan tools.com.np',
                'phone' => '+977-1-7778901',
                'address' => 'Patan Industrial Area, Lalitpur 44700, Nepal',
                'category' => 'other',
                'lead_time_days' => 7,
                'rating' => 4.10,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
