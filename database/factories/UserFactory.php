<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'phone' => fake()->phoneNumber(),
            'is_active' => true,
        ];
    }

    public function withOrganization(?Organization $org = null, string $role = 'owner'): static
    {
        return $this->afterCreating(function (User $user) use ($org, $role) {
            $org ??= Organization::factory()->create(['owner_id' => $user->id]);
            $org->users()->syncWithoutDetaching([$user->id => ['role' => $role]]);
            $user->forceFill(['current_organization_id' => $org->id])->saveQuietly();
        });
    }
}
