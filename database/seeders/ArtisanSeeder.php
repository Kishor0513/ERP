<?php

namespace Database\Seeders;

use App\Models\Artisan;
use Illuminate\Database\Seeder;

class ArtisanSeeder extends Seeder
{
    public function run(): void
    {
        $artisans = [
            [
                'name' => 'Kamala Devi',
                'location' => 'Bhaktapur',
                'phone' => '+977-9841000001',
                'skills' => json_encode(['felting', 'needle_felting']),
                'certification_status' => 'Certified Level 2',
                'bank_details' => 'Nabil Bank - 0123456789',
                'payout_method' => 'bank_transfer',
                'join_date' => '2019-03-15',
                'status' => 'active',
            ],
            [
                'name' => 'Sunita Tamang',
                'location' => 'Lalitpur',
                'phone' => '+977-9841000002',
                'skills' => json_encode(['felting', 'stitching']),
                'certification_status' => 'Certified Level 1',
                'bank_details' => 'Global IME Bank - 0234567890',
                'payout_method' => 'bank_transfer',
                'join_date' => '2020-01-10',
                'status' => 'active',
            ],
            [
                'name' => 'Rita Shrestha',
                'location' => 'Kathmandu',
                'phone' => '+977-9841000003',
                'skills' => json_encode(['felting', 'dyeing']),
                'certification_status' => 'Certified Level 2',
                'bank_details' => 'Nepal Investment Bank - 0345678901',
                'payout_method' => 'bank_transfer',
                'join_date' => '2019-08-20',
                'status' => 'active',
            ],
            [
                'name' => 'Maya Gurung',
                'location' => 'Bhaktapur',
                'phone' => '+977-9841000004',
                'skills' => json_encode(['needle_felting', 'stitching']),
                'certification_status' => 'Certified Level 1',
                'bank_details' => 'Prabhu Bank - 0456789012',
                'payout_method' => 'mobile_money',
                'join_date' => '2021-05-12',
                'status' => 'active',
            ],
            [
                'name' => 'Laxmi Bajracharya',
                'location' => 'Kathmandu',
                'phone' => '+977-9841000005',
                'skills' => json_encode(['felting']),
                'certification_status' => 'Certified Level 3',
                'bank_details' => 'Sanima Bank - 0567890123',
                'payout_method' => 'bank_transfer',
                'join_date' => '2019-01-05',
                'status' => 'active',
            ],
            [
                'name' => 'Deepa Thapa',
                'location' => 'Pokhara',
                'phone' => '+977-9841000006',
                'skills' => json_encode(['dyeing', 'felting']),
                'certification_status' => 'Certified Level 1',
                'bank_details' => 'Nabil Bank - 0678901234',
                'payout_method' => 'bank_transfer',
                'join_date' => '2022-03-08',
                'status' => 'active',
            ],
            [
                'name' => 'Sarita Maharjan',
                'location' => 'Lalitpur',
                'phone' => '+977-9841000007',
                'skills' => json_encode(['stitching', 'assembly']),
                'certification_status' => 'Certified Level 2',
                'bank_details' => 'Global IME Bank - 0789012345',
                'payout_method' => 'bank_transfer',
                'join_date' => '2020-09-14',
                'status' => 'active',
            ],
            [
                'name' => 'Anita Rai',
                'location' => 'Bhaktapur',
                'phone' => '+977-9841000008',
                'skills' => json_encode(['felting', 'needle_felting']),
                'certification_status' => 'Certified Level 2',
                'bank_details' => 'Nepal Investment Bank - 0890123456',
                'payout_method' => 'mobile_money',
                'join_date' => '2021-07-22',
                'status' => 'active',
            ],
            [
                'name' => 'Gita Basnet',
                'location' => 'Kathmandu',
                'phone' => '+977-9841000009',
                'skills' => json_encode(['stitching', 'assembly']),
                'certification_status' => 'Certified Level 1',
                'bank_details' => 'Prabhu Bank - 0901234567',
                'payout_method' => 'bank_transfer',
                'join_date' => '2023-02-18',
                'status' => 'active',
            ],
            [
                'name' => 'Reshma Limbu',
                'location' => 'Bhaktapur',
                'phone' => '+977-9841000010',
                'skills' => json_encode(['felting']),
                'certification_status' => null,
                'bank_details' => null,
                'payout_method' => 'cash',
                'join_date' => '2020-06-01',
                'status' => 'inactive',
            ],
        ];

        foreach ($artisans as $artisan) {
            Artisan::create($artisan);
        }
    }
}
