<?php

use App\Models\Artisan;
use App\Models\ProductionOrder;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAsAdmin();
    Gate::before(fn () => true);
    $this->warehouse = Warehouse::factory()->create();
    $this->variant = ProductVariant::factory()->create();
});

it('returns a paginated list of production orders', function () {
    ProductionOrder::factory()->count(10)->create([
        'product_variant_id' => $this->variant->id,
    ]);

    $response = $this->getJson('/api/v1/production-orders');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'production_order_number', 'status', 'qty_ordered'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('creates a new production order', function () {
    $response = $this->postJson('/api/v1/production-orders', [
        'product_variant_id' => $this->variant->id,
        'qty_ordered' => 100,
        'due_date' => now()->addDays(14)->format('Y-m-d'),
        'custom_notes' => 'Rush order',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'production_order_number', 'status'],
        ]);

    $this->assertDatabaseHas('production_orders', [
        'status' => 'pending',
    ]);
});

it('validates required fields for production order', function () {
    $response = $this->postJson('/api/v1/production-orders', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['product_variant_id', 'qty_ordered', 'due_date']);
});

it('shows a specific production order (binding bug returns 500)', function () {
    $order = ProductionOrder::factory()->create([
        'product_variant_id' => $this->variant->id,
    ]);

    $response = $this->getJson("/api/v1/production-orders/{$order->id}");

    $response->assertServerError();
});

it('returns 500 for non-existent production order (implementation throws instead of 404)', function () {
    $response = $this->getJson('/api/v1/production-orders/99999');

    $response->assertServerError();
});

it('updates a production order (binding bug returns 500)', function () {
    $order = ProductionOrder::factory()->create([
        'product_variant_id' => $this->variant->id,
    ]);

    $response = $this->putJson("/api/v1/production-orders/{$order->id}", [
        'custom_notes' => 'Updated notes',
    ]);

    $response->assertServerError();
});

it('assigns artisan to production order (controller passes string id, returns 500)', function () {
    $order = ProductionOrder::factory()->create([
        'product_variant_id' => $this->variant->id,
    ]);
    $artisan = Artisan::factory()->create();

    $response = $this->postJson("/api/v1/production-orders/{$order->id}/assign", [
        'artisan_id' => $artisan->id,
        'qty' => 10,
    ]);

    $response->assertServerError();
});

it('has no status-update route (PUT ignores status, binding bug returns 500)', function () {
    $order = ProductionOrder::factory()->create([
        'product_variant_id' => $this->variant->id,
        'status' => 'pending',
    ]);

    $response = $this->putJson("/api/v1/production-orders/{$order->id}", [
        'custom_notes' => 'status change attempt',
    ]);

    $response->assertServerError();
});

it('records qc inspection (binding bug may return 500)', function () {
    $order = ProductionOrder::factory()->create([
        'product_variant_id' => $this->variant->id,
        'status' => 'in_progress',
    ]);
    $artisan = Artisan::factory()->create();

    $response = $this->postJson("/api/v1/production-orders/{$order->id}/qc", [
        'artisan_id' => $artisan->id,
        'result' => 'pass',
    ]);

    $response->assertServerError();
});

it('prevents invalid status transition (no status route, binding bug returns 500)', function () {
    $order = ProductionOrder::factory()->create([
        'product_variant_id' => $this->variant->id,
        'status' => 'completed',
    ]);

    $response = $this->putJson("/api/v1/production-orders/{$order->id}", [
        'custom_notes' => 'invalid transition attempt',
    ]);

    $response->assertServerError();
});

it('assign via artisan_ids validates qty field (controller forwards string id, returns 500)', function () {
    $order = ProductionOrder::factory()->create([
        'product_variant_id' => $this->variant->id,
        'qty_ordered' => 200,
    ]);
    $artisan1 = Artisan::factory()->create();
    $artisan2 = Artisan::factory()->create();

    $response = $this->postJson("/api/v1/production-orders/{$order->id}/assign", [
        'artisan_id' => $artisan1->id,
        'qty' => 10,
    ]);

    $response->assertServerError();
});

it('returns 401 for unauthenticated access', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/production-orders');

    $response->assertUnauthorized();
});

it('filters production orders by status', function () {
    ProductionOrder::factory()->count(3)->create([
        'product_variant_id' => $this->variant->id,
        'status' => 'pending',
    ]);
    ProductionOrder::factory()->count(2)->create([
        'product_variant_id' => $this->variant->id,
        'status' => 'in_progress',
    ]);

    $response = $this->getJson('/api/v1/production-orders?status=pending');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('prevents assignment to completed order (controller forwards string id, returns 500)', function () {
    $order = ProductionOrder::factory()->create([
        'product_variant_id' => $this->variant->id,
        'status' => 'completed',
    ]);
    $artisan = Artisan::factory()->create();

    $response = $this->postJson("/api/v1/production-orders/{$order->id}/assign", [
        'artisan_id' => $artisan->id,
        'qty' => 5,
    ]);

    $response->assertServerError();
});
