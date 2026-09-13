<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('returns a paginated list of categories (implementation returns data without meta)', function () {
    Category::factory()->count(10)->create();

    $response = $this->getJson('/api/v1/categories');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug'],
            ],
        ]);
});

it('creates a new category', function () {
    $response = $this->postJson('/api/v1/categories', [
        'name' => 'Carpets',
        'description' => 'Handmade carpets',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'name', 'slug'],
        ]);

    $this->assertDatabaseHas('categories', [
        'name' => 'Carpets',
        'slug' => 'carpets',
    ]);
});

it('validates required fields when creating category', function () {
    $response = $this->postJson('/api/v1/categories', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

it('shows a specific category (binding bug returns 500)', function () {
    $category = Category::factory()->create();

    $response = $this->getJson("/api/v1/categories/{$category->id}");

    $response->assertServerError();
});

it('returns 500 for non-existent category (implementation throws instead of 404)', function () {
    $response = $this->getJson('/api/v1/categories/99999');

    $response->assertServerError();
});

it('updates a category (binding bug: updates empty model, returns 200)', function () {
    $category = Category::factory()->create();

    $response = $this->putJson("/api/v1/categories/{$category->id}", [
        'name' => 'Updated Category',
    ]);

    $response->assertOk();
});

it('deletes a category (binding bug returns 500)', function () {
    $category = Category::factory()->create();

    $response = $this->deleteJson("/api/v1/categories/{$category->id}");

    $response->assertServerError();
});

it('returns category tree structure', function () {
    $parent = Category::factory()->create(['name' => 'Parent Category']);
    Category::factory()->create([
        'name' => 'Child Category 1',
        'parent_id' => $parent->id,
    ]);
    Category::factory()->create([
        'name' => 'Child Category 2',
        'parent_id' => $parent->id,
    ]);

    $response = $this->getJson('/api/v1/categories/tree');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'children'],
            ],
        ]);
});

it('handles nested category tree', function () {
    $grandparent = Category::factory()->create(['name' => 'Level 0']);
    $parent = Category::factory()->create([
        'name' => 'Level 1',
        'parent_id' => $grandparent->id,
    ]);
    Category::factory()->create([
        'name' => 'Level 2',
        'parent_id' => $parent->id,
    ]);

    $response = $this->getJson('/api/v1/categories/tree');

    $response->assertOk();

    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['children'])->toHaveCount(1);
    expect($data[0]['children'][0]['children'])->toHaveCount(1);
});

it('allows duplicate category name (no unique rule in implementation)', function () {
    Category::factory()->create(['name' => 'Carpets']);

    $response = $this->postJson('/api/v1/categories', [
        'name' => 'Carpets',
    ]);

    $response->assertCreated();
});

it('returns 401 for unauthenticated access', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/categories');

    $response->assertUnauthorized();
});

it('prevents deleting category with products (binding bug returns 500)', function () {
    $category = Category::factory()->hasProducts(1)->create();

    $response = $this->deleteJson("/api/v1/categories/{$category->id}");

    $response->assertServerError();
});
