<?php

namespace Tests;

use App\Models\Organization;
use App\Models\User;
use App\Support\CurrentOrganization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected ?User $adminUser = null;

    protected ?Organization $adminOrg = null;

    protected function setUp(): void
    {
        parent::setUp();
        CurrentOrganization::reset();
    }

    protected function tearDown(): void
    {
        CurrentOrganization::reset();
        parent::tearDown();
    }

    protected function actingAsAdmin(array $userAttrs = []): static
    {
        $user = User::factory()->withOrganization()->create($userAttrs);
        $user->refresh();
        $orgId = (int) $user->current_organization_id;

        $this->adminUser = $user;
        $this->adminOrg = Organization::find($orgId);

        CurrentOrganization::override($orgId);
        Sanctum::actingAs($user, ['*']);

        $this->withHeader('X-Organization-ID', (string) $orgId);
        $this->withHeader('Accept', 'application/json');

        return $this;
    }

    protected function actingAsUser(?Organization $org = null): static
    {
        return $this->actingAsAdmin();
    }
}
