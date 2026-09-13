<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $approver = User::first();

        $expenses = [
            ['category' => 'raw_materials', 'description' => 'Wool consignment top-up', 'amount' => 1250.00, 'days_ago' => 2],
            ['category' => 'shipping', 'description' => 'DHL export freight - EU batch', 'amount' => 480.50, 'days_ago' => 5],
            ['category' => 'payroll', 'description' => 'Artisan overtime payout', 'amount' => 860.00, 'days_ago' => 7],
            ['category' => 'overhead', 'description' => 'Workshop electricity bill', 'amount' => 210.75, 'days_ago' => 10],
            ['category' => 'marketing', 'description' => 'Trade fair booth fee', 'amount' => 1500.00, 'days_ago' => 14],
            ['category' => 'other', 'description' => 'Packaging materials restock', 'amount' => 320.00, 'days_ago' => 16],
        ];

        foreach ($expenses as $e) {
            Expense::create([
                'category' => $e['category'],
                'description' => $e['description'],
                'amount' => $e['amount'],
                'currency' => 'USD',
                'incurred_at' => now()->subDays($e['days_ago'])->toDateString(),
                'approved_by' => $approver?->id,
            ]);
        }
    }
}
