<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@feltandyarn.com',
            'password' => $password,
            'phone' => '+1-555-0100',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('Super Admin');

        $productionMgr = User::create([
            'name' => 'Production Manager',
            'email' => 'production@example.com',
            'password' => $password,
            'phone' => '+1-555-0101',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $productionMgr->assignRole('Production Manager');

        $salesMgr = User::create([
            'name' => 'Sales Manager',
            'email' => 'sales@example.com',
            'password' => $password,
            'phone' => '+1-555-0102',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $salesMgr->assignRole('Sales Manager');

        $inventoryMgr = User::create([
            'name' => 'Inventory Manager',
            'email' => 'inventory@example.com',
            'password' => $password,
            'phone' => '+1-555-0103',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $inventoryMgr->assignRole('Inventory Manager');

        $finance = User::create([
            'name' => 'Finance User',
            'email' => 'finance@example.com',
            'password' => $password,
            'phone' => '+1-555-0104',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $finance->assignRole('Finance');

        $hr = User::create([
            'name' => 'HR Officer',
            'email' => 'hr@example.com',
            'password' => $password,
            'phone' => '+1-555-0105',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $hr->assignRole('HR Officer');

        $qc = User::create([
            'name' => 'QC Inspector',
            'email' => 'qc@example.com',
            'password' => $password,
            'phone' => '+1-555-0106',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $qc->assignRole('QC Inspector');

        $logistics = User::create([
            'name' => 'Logistics Officer',
            'email' => 'logistics@example.com',
            'password' => $password,
            'phone' => '+977-9841234574',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $logistics->assignRole('Logistics Officer');
    }
}
