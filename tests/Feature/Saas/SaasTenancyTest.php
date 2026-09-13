<?php

use App\Models\Category;
use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers user with organization and trial', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'SaaS Owner',
        'email' => 'owner@saas.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'organization_name' => 'Acme Inc',
    ]);

    $response->assertCreated()->assertJsonPath('success', true);
    $this->assertDatabaseHas('organizations', ['name' => 'Acme Inc']);
    $this->assertDatabaseHas('organization_user', ['role' => 'owner']);
    expect(Organization::first()->onTrial())->toBeTrue();
});

it('isolates tenant data by organization', function () {
    $ownerA = User::factory()->create();
    $orgA = Organization::factory()->create(['owner_id' => $ownerA->id]);
    $orgA->users()->attach($ownerA->id, ['role' => 'owner']);
    $ownerA->forceFill(['current_organization_id' => $orgA->id])->saveQuietly();

    $ownerB = User::factory()->create();
    $orgB = Organization::factory()->create(['owner_id' => $ownerB->id]);
    $orgB->users()->attach($ownerB->id, ['role' => 'owner']);
    $ownerB->forceFill(['current_organization_id' => $orgB->id])->saveQuietly();

    $catA = Category::withoutGlobalScope('organization')->create(['organization_id' => $orgA->id, 'name' => 'Cat A', 'slug' => 'cat-a-'.uniqid(), 'is_active' => true]);
    $catB = Category::withoutGlobalScope('organization')->create(['organization_id' => $orgB->id, 'name' => 'Cat B', 'slug' => 'cat-b-'.uniqid(), 'is_active' => true]);

    Product::withoutGlobalScope('organization')->create(['organization_id' => $orgA->id, 'category_id' => $catA->id, 'name' => 'Prod A', 'slug' => 'prod-a-'.uniqid(), 'base_price' => 10]);
    Product::withoutGlobalScope('organization')->create(['organization_id' => $orgB->id, 'category_id' => $catB->id, 'name' => 'Prod B', 'slug' => 'prod-b-'.uniqid(), 'base_price' => 20]);

    $this->actingAs($ownerA);
    expect(Product::count())->toBe(1)->and(Product::first()->name)->toBe('Prod A');

    $this->actingAs($ownerB);
    expect(Product::count())->toBe(1)->and(Product::first()->name)->toBe('Prod B');
});

it('exposes billing status and organizations endpoints', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner']);
    $user->forceFill(['current_organization_id' => $org->id])->saveQuietly();
    $token = $user->createToken('t')->plainTextToken;

    $this->withHeader('Authorization', "Bearer $token")->getJson('/api/v1/organizations')->assertOk();
    $this->withHeader('Authorization', "Bearer $token")->getJson('/api/v1/billing')->assertOk()->assertJsonPath('success', true);
});
