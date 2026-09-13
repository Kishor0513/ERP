<?php

use App\Models\ProductVariant;
use App\Models\RawMaterial;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAsAdmin();
    $this->warehouse = Warehouse::factory()->create();
});

it('returns a paginated list of stock movements', function () {
    StockMovement::factory()->count(15)->create([
        'warehouse_id' => $this->warehouse->id,
    ]);

    $response = $this->getJson('/api/v1/stock-movements');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'type', 'qty', 'warehouse_id'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('creates a stock adjustment (inbound)', function () {
    $variant = ProductVariant::factory()->create();

    $response = $this->postJson('/api/v1/stock-movements', [
        'item_type' => 'product_variant',
        'item_id' => $variant->id,
        'warehouse_id' => $this->warehouse->id,
        'type' => 'adjustment_in',
        'qty' => 100,
        'notes' => 'Initial stock',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'type', 'qty'],
        ]);

    $this->assertDatabaseHas('stock_movements', [
        'type' => 'adjustment_in',
        'qty' => 100,
    ]);
});

it('validates required fields for stock movement', function () {
    $response = $this->postJson('/api/v1/stock-movements', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['item_type', 'item_id', 'warehouse_id', 'type', 'qty']);
});

it('creates a stock movement of type transfer', function () {
    $variant = ProductVariant::factory()->create();
    $destinationWarehouse = Warehouse::factory()->create();

    $response = $this->postJson('/api/v1/stock-movements', [
        'item_type' => 'product_variant',
        'item_id' => $variant->id,
        'warehouse_id' => $this->warehouse->id,
        'type' => 'transfer_in',
        'qty' => 50,
        'notes' => 'Transfer to main warehouse',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('stock_movements', [
        'type' => 'transfer_in',
        'qty' => 50,
    ]);
});

it('validates transfer type must be a known type', function () {
    $variant = ProductVariant::factory()->create();

    $response = $this->postJson('/api/v1/stock-movements', [
        'item_type' => 'product_variant',
        'item_id' => $variant->id,
        'warehouse_id' => $this->warehouse->id,
        'type' => 'transfer',
        'qty' => 50,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['type']);
});

it('validates positive quantity', function () {
    $variant = ProductVariant::factory()->create();

    $response = $this->postJson('/api/v1/stock-movements', [
        'item_type' => 'product_variant',
        'item_id' => $variant->id,
        'warehouse_id' => $this->warehouse->id,
        'type' => 'adjustment_in',
        'qty' => -10,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['qty']);
});

it('shows low stock alerts', function () {
    RawMaterial::factory()->create([
        'current_stock' => 5,
        'reorder_point' => 10,
    ]);

    $response = $this->getJson('/api/v1/stock-movements/alerts');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['product_variants', 'raw_materials'],
        ]);
});

it('filters stock movements by warehouse', function () {
    $warehouse1 = Warehouse::factory()->create();
    $warehouse2 = Warehouse::factory()->create();

    StockMovement::factory()->count(3)->create([
        'warehouse_id' => $warehouse1->id,
    ]);
    StockMovement::factory()->count(2)->create([
        'warehouse_id' => $warehouse2->id,
    ]);

    $response = $this->getJson("/api/v1/stock-movements?warehouse_id={$warehouse1->id}");

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('filters stock movements by type', function () {
    StockMovement::factory()->create([
        'warehouse_id' => $this->warehouse->id,
        'type' => 'adjustment_in',
    ]);
    StockMovement::factory()->create([
        'warehouse_id' => $this->warehouse->id,
        'type' => 'adjustment_out',
    ]);

    $response = $this->getJson('/api/v1/stock-movements?type=adjustment_in');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('returns 401 for unauthenticated access', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/stock-movements');

    $response->assertUnauthorized();
});

it('creates stock adjustment out', function () {
    $variant = ProductVariant::factory()->create(['stock_quantity' => 100]);

    $response = $this->postJson('/api/v1/stock-movements', [
        'item_type' => 'product_variant',
        'item_id' => $variant->id,
        'warehouse_id' => $this->warehouse->id,
        'type' => 'adjustment_out',
        'qty' => 25,
        'notes' => 'Damaged goods',
    ]);

    $response->assertCreated();
});
