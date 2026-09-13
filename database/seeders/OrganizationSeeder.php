<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use App\Support\CurrentOrganization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::firstOrCreate(
            ['slug' => 'felt-and-yarn'],
            [
                'name' => 'Felt and Yarn',
                'trial_ends_at' => now()->addDays((int) config('saas.trial_days', 14)),
            ]
        );

        if (! $org->owner_id && $owner = User::first()) {
            $org->forceFill(['owner_id' => $owner->id])->saveQuietly();
        }

        CurrentOrganization::override($org->id);

        $users = User::all();
        foreach ($users as $user) {
            $org->users()->syncWithoutDetaching([$user->id => ['role' => $user->id === $org->owner_id ? 'owner' : 'member']]);
            if (! $user->current_organization_id) {
                $user->forceFill(['current_organization_id' => $org->id])->saveQuietly();
            }
        }
    }
}
