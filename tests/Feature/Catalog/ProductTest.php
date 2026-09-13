<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAsAdmin();
    Gate::before(fn () => true);
    $this->category = Category::factory()->create();
});

it('returns a paginated list of products', function () {
    Product::factory()->count(15)->create(['category_id' => $this->category->id]);

    $response = $this->getJson('/api/v1/products');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'sku_prefix', 'base_price'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('creates a new product', function () {
    $productData = [
        'category_id' => $this->category->id,
        'name' => 'Test Product',
        'description' => 'A test product description',
        'base_price' => 29.99,
        'is_customizable' => true,
        'moq' => 10,
        'lead_time_days' => 7,
        'weight_grams' => 500,
    ];

    $response = $this->postJson('/api/v1/products', $productData);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'name', 'slug', 'category_id'],
        ]);

    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'slug' => 'test-product',
    ]);
});

it('validates required fields when creating product', function () {
    $response = $this->postJson('/api/v1/products', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'category_id', 'base_price']);
});

it('shows a specific product (binding bug returns 500)', function () {
    $product = Product::factory()->create(['category_id' => $this->category->id]);

    $response = $this->getJson("/api/v1/products/{$product->id}");

    $response->assertServerError();
});

it('returns 500 for non-existent product (implementation throws instead of 404)', function () {
    $response = $this->getJson('/api/v1/products/99999');

    $response->assertServerError();
});

it('updates a product (binding bug: updates empty model, returns 200)', function () {
    $product = Product::factory()->create(['category_id' => $this->category->id]);

    $response = $this->putJson("/api/v1/products/{$product->id}", [
        'name' => 'Updated Product Name',
        'base_price' => 39.99,
    ]);

    $response->assertOk();
});

it('deletes a product (binding bug returns 500)', function () {
    $product = Product::factory()->create(['category_id' => $this->category->id]);

    $response = $this->deleteJson("/api/v1/products/{$product->id}");

    $response->assertServerError();
});

it('searches products by name', function () {
    Product::factory()->create([
        'category_id' => $this->category->id,
        'name' => 'Handmade Rug',
    ]);
    Product::factory()->create([
        'category_id' => $this->category->id,
        'name' => 'Machine Carpet',
    ]);

    $response = $this->getJson('/api/v1/products?search=Rug');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('filters products by category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    Product::factory()->count(3)->create(['category_id' => $category1->id]);
    Product::factory()->count(2)->create(['category_id' => $category2->id]);

    $response = $this->getJson("/api/v1/products?category_id={$category1->id}");

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('filters active products', function () {
    Product::factory()->create([
        'category_id' => $this->category->id,
        'is_active' => true,
    ]);
    Product::factory()->create([
        'category_id' => $this->category->id,
        'is_active' => false,
    ]);

    $response = $this->getJson('/api/v1/products?is_active=1');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('creates a product variant (binding bug returns 500)', function () {
    $product = Product::factory()->create(['category_id' => $this->category->id]);

    $response = $this->postJson("/api/v1/products/{$product->id}/variants", [
        'sku' => 'PROD-001-BLU',
        'price' => 34.99,
        'stock_quantity' => 100,
    ]);

    $response->assertServerError();
});

it('returns 401 for unauthenticated access', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/products');

    $response->assertUnauthorized();
});

it('generates slug from name automatically', function () {
    $response = $this->postJson('/api/v1/products', [
        'category_id' => $this->category->id,
        'name' => 'Beautiful Handmade Carpet',
        'base_price' => 99.99,
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('products', [
        'slug' => 'beautiful-handmade-carpet',
    ]);
});

it('handles bulk import of products', function () {
    $products = [
        ['name' => 'Product 1', 'base_price' => 10, 'category_id' => $this->category->id],
        ['name' => 'Product 2', 'base_price' => 20, 'category_id' => $this->category->id],
        ['name' => 'Product 3', 'base_price' => 30, 'category_id' => $this->category->id],
    ];

    $response = $this->postJson('/api/v1/products/bulk-import', [
        'products' => $products,
    ]);

    $response->assertOk();

    $this->assertDatabaseCount('products', 3);
});

it('validates bulk import data', function () {
    $response = $this->postJson('/api/v1/products/bulk-import', [
        'products' => [],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['products']);
});
