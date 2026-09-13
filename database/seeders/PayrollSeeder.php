<?php

namespace Database\Seeders;

use App\Models\PayrollRun;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        $payrollRun = PayrollRun::create([
            'period_start' => '2024-01-01',
            'period_end' => '2024-01-31',
            'status' => 'draft',
            'total_amount' => 0.00,
            'currency' => 'USD',
            'approved_by' => null,
            'paid_at' => null,
        ]);

        $items = [
            [
                'artisan_id' => 1,
                'staff_user_id' => null,
                'gross_amount' => 250.00,
                'deductions' => 0.00,
                'net_amount' => 250.00,
                'units_completed' => 2500,
                'period_notes' => 'Felting and needle felting work.',
            ],
            [
                'artisan_id' => 2,
                'staff_user_id' => null,
                'gross_amount' => 220.00,
                'deductions' => 0.00,
                'net_amount' => 220.00,
                'units_completed' => 2200,
                'period_notes' => 'Felting and stitching work.',
            ],
            [
                'artisan_id' => 3,
                'staff_user_id' => null,
                'gross_amount' => 180.00,
                'deductions' => 0.00,
                'net_amount' => 180.00,
                'units_completed' => 1800,
                'period_notes' => 'Felting and dyeing work.',
            ],
            [
                'artisan_id' => 5,
                'staff_user_id' => null,
                'gross_amount' => 300.00,
                'deductions' => 0.00,
                'net_amount' => 300.00,
                'units_completed' => 3000,
                'period_notes' => 'Senior artisan felting work.',
            ],
            [
                'artisan_id' => 7,
                'staff_user_id' => null,
                'gross_amount' => 195.00,
                'deductions' => 0.00,
                'net_amount' => 195.00,
                'units_completed' => 1300,
                'period_notes' => 'Stitching and assembly work.',
            ],
        ];

        foreach ($items as $item) {
            $payrollRun->items()->create($item);
        }

        $total = collect($items)->sum('gross_amount');
        $payrollRun->update(['total_amount' => $total]);
    }
}
