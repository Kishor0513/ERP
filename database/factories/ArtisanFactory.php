<?php

namespace Database\Factories;

use App\Models\Artisan;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArtisanFactory extends Factory
{
    protected $model = Artisan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'location' => fake()->city(),
            'phone' => fake()->phoneNumber(),
            'skills' => ['weaving', 'dyeing'],
            'certification_status' => fake()->randomElement(['certified', 'pending', null]),
            'payout_method' => fake()->randomElement(['bank_transfer', 'cash', null]),
            'join_date' => fake()->date(),
            'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }
}
