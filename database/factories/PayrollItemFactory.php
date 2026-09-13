<?php

namespace Database\Factories;

use App\Models\Artisan;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollItemFactory extends Factory
{
    protected $model = PayrollItem::class;

    public function definition(): array
    {
        $gross = fake()->randomFloat(2, 100, 5000);
        $deductions = fake()->randomFloat(2, 0, 200);

        return [
            'payroll_run_id' => PayrollRun::factory(),
            'artisan_id' => Artisan::factory(),
            'gross_amount' => $gross,
            'deductions' => $deductions,
            'net_amount' => max(0, $gross - $deductions),
            'units_completed' => fake()->optional()->numberBetween(1, 100),
            'period_notes' => fake()->optional()->sentence(),
        ];
    }
}
