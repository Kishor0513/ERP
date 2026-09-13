<?php

namespace Database\Seeders;

use App\Models\WholesaleAccount;
use Illuminate\Database\Seeder;

class WholesaleAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'company_name' => 'Woolly Friends Pet Co',
                'contact_name' => 'Sarah Mitchell',
                'email' => 'sarah@woollyfriends.com',
                'phone' => '+1-555-0101',
                'address' => '456 Pet Lane',
                'city' => 'Portland',
                'state' => 'OR',
                'country' => 'USA',
                'postal_code' => '97201',
                'business_type' => 'Pet Retail',
                'website' => 'https://woollyfriends.com',
                'expected_monthly_volume' => 500,
                'status' => 'approved',
                'payment_terms' => 'deposit_only',
                'credit_limit' => 5000.00,
                'notes' => 'Specializes in eco-friendly pet products. First order 2023.',
            ],
            [
                'company_name' => 'Craft Haven Boutique',
                'contact_name' => 'Emily Chen',
                'email' => 'emily@crfthaven.com',
                'phone' => '+1-555-0202',
                'address' => '789 Craft Street',
                'city' => 'Austin',
                'state' => 'TX',
                'country' => 'USA',
                'postal_code' => '78701',
                'business_type' => 'Craft Retail',
                'website' => 'https://crfthaven.com',
                'expected_monthly_volume' => 800,
                'status' => 'approved',
                'payment_terms' => 'net_30',
                'credit_limit' => 10000.00,
                'notes' => 'Bulk buyer of felt balls and craft supplies.',
            ],
            [
                'company_name' => 'Nordic Home Decor',
                'contact_name' => 'Erik Larsson',
                'email' => 'erik@nordichome.se',
                'phone' => '+46-8-555-0303',
                'address' => '12 Storgatan',
                'city' => 'Stockholm',
                'state' => null,
                'country' => 'Sweden',
                'postal_code' => '11151',
                'business_type' => 'Home Decor Retail',
                'website' => 'https://nordichome.se',
                'expected_monthly_volume' => 1200,
                'status' => 'approved',
                'payment_terms' => 'net_60',
                'credit_limit' => 15000.00,
                'notes' => 'Premium home decor retailer. High volume orders.',
            ],
            [
                'company_name' => 'Mountain Kids Store',
                'contact_name' => 'Lisa Park',
                'email' => 'lisa@mountainkids.com',
                'phone' => '+1-555-0404',
                'address' => '321 Mountain Road',
                'city' => 'Denver',
                'state' => 'CO',
                'country' => 'USA',
                'postal_code' => '80201',
                'business_type' => 'Children Retail',
                'website' => 'https://mountainkids.com',
                'expected_monthly_volume' => 300,
                'status' => 'pending',
                'payment_terms' => null,
                'credit_limit' => null,
                'notes' => 'New application pending review.',
            ],
            [
                'company_name' => 'Eco Home Australia',
                'contact_name' => 'James Wright',
                'email' => 'james@ecohome.com.au',
                'phone' => '+61-2-555-0505',
                'address' => '45 Green Avenue',
                'city' => 'Sydney',
                'state' => 'NSW',
                'country' => 'Australia',
                'postal_code' => '2000',
                'business_type' => 'Home Goods Retail',
                'website' => 'https://ecohome.com.au',
                'expected_monthly_volume' => 600,
                'status' => 'pending',
                'payment_terms' => null,
                'credit_limit' => null,
                'notes' => 'Interested in eco-friendly home products.',
            ],
        ];

        foreach ($accounts as $account) {
            WholesaleAccount::create($account);
        }
    }
}
