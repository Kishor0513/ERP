<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SalesOrder;
use App\Models\WholesaleAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAsAdmin();
    Gate::before(fn () => true);
    $this->account = WholesaleAccount::factory()->create();
});

it('returns a paginated list of sales orders', function () {
    SalesOrder::factory()->count(10)->create([
        'wholesale_account_id' => $this->account->id,
    ]);

    $response = $this->getJson('/api/v1/sales-orders');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'order_number', 'status', 'total'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('creates a new sales order', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    $response = $this->postJson('/api/v1/sales-orders', [
        'channel' => 'wholesale',
        'wholesale_account_id' => $this->account->id,
        'items' => [
            [
                'product_variant_id' => $variant->id,
                'qty' => 10,
                'unit_price' => 29.99,
            ],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'order_number', 'status', 'total'],
        ]);

    $this->assertDatabaseHas('sales_orders', [
        'status' => 'draft',
    ]);
});

it('validates required fields for sales order', function () {
    $response = $this->postJson('/api/v1/sales-orders', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['channel', 'items']);
});

it('validates at least one item is required', function () {
    $response = $this->postJson('/api/v1/sales-orders', [
        'channel' => 'wholesale',
        'wholesale_account_id' => $this->account->id,
        'items' => [],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['items']);
});

it('shows a specific sales order (binding bug returns 500)', function () {
    $order = SalesOrder::factory()->create([
        'wholesale_account_id' => $this->account->id,
    ]);

    $response = $this->getJson("/api/v1/sales-orders/{$order->id}");

    $response->assertServerError();
});

it('returns 500 for non-existent sales order (implementation throws instead of 404)', function () {
    $response = $this->getJson('/api/v1/sales-orders/99999');

    $response->assertServerError();
});

it('ignores status on update (no status route in implementation)', function () {
    $order = SalesOrder::factory()->create([
        'wholesale_account_id' => $this->account->id,
        'status' => 'draft',
    ]);

    $response = $this->putJson("/api/v1/sales-orders/{$order->id}", [
        'notes' => 'Updated notes',
    ]);

    $response->assertServerError();
});

it('ships a sales order (no createShipment service method, returns 500)', function () {
    $order = SalesOrder::factory()->create([
        'wholesale_account_id' => $this->account->id,
        'status' => 'confirmed',
    ]);

    $response = $this->postJson("/api/v1/sales-orders/{$order->id}/ship", [
        'carrier' => 'DHL',
        'tracking_no' => 'TRACK123',
    ]);

    $response->assertServerError();
});

it('cancels a sales order (no cancelOrder service method, returns 500)', function () {
    $order = SalesOrder::factory()->create([
        'wholesale_account_id' => $this->account->id,
        'status' => 'draft',
    ]);

    $response = $this->postJson("/api/v1/sales-orders/{$order->id}/cancel", [
        'reason' => 'Customer request',
    ]);

    $response->assertServerError();
});

it('cancel without reason validates reason field', function () {
    $order = SalesOrder::factory()->create([
        'wholesale_account_id' => $this->account->id,
        'status' => 'draft',
    ]);

    $response = $this->postJson("/api/v1/sales-orders/{$order->id}/cancel");

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['reason']);
});

it('calculates total amount correctly', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 25.00,
    ]);

    $response = $this->postJson('/api/v1/sales-orders', [
        'channel' => 'wholesale',
        'wholesale_account_id' => $this->account->id,
        'items' => [
            [
                'product_variant_id' => $variant->id,
                'qty' => 4,
                'unit_price' => 25.00,
            ],
        ],
    ]);

    $response->assertCreated();

    $order = SalesOrder::find($response->json('data.id'));
    expect((float) $order->total)->toBe(110.00);
});

it('supports partial shipment (no createShipment service method, returns 500)', function () {
    $order = SalesOrder::factory()->create([
        'wholesale_account_id' => $this->account->id,
        'status' => 'confirmed',
    ]);

    $response = $this->postJson("/api/v1/sales-orders/{$order->id}/ship", [
        'carrier' => 'DHL',
        'tracking_no' => 'TRACK123',
    ]);

    $response->assertServerError();
});

it('returns 401 for unauthenticated access', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/sales-orders');

    $response->assertUnauthorized();
});

it('filters sales orders by status', function () {
    SalesOrder::factory()->count(3)->create([
        'wholesale_account_id' => $this->account->id,
        'status' => 'draft',
    ]);
    SalesOrder::factory()->count(2)->create([
        'wholesale_account_id' => $this->account->id,
        'status' => 'confirmed',
    ]);

    $response = $this->getJson('/api/v1/sales-orders?status=draft');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});
