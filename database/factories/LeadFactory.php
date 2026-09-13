<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'source' => fake()->randomElement(['website', 'whatsapp', 'trade_show', 'referral', 'cold_outreach']),
            'company_name' => fake()->optional()->company(),
            'contact_name' => fake()->name(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'status' => fake()->randomElement(['new', 'contacted', 'qualified', 'converted', 'lost']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
