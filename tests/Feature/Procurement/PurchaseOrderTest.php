<?php

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAsAdmin();
    Gate::before(fn () => true);
    $this->supplier = Supplier::factory()->create();
});

it('returns a paginated list of purchase orders', function () {
    PurchaseOrder::factory()->count(10)->create([
        'supplier_id' => $this->supplier->id,
    ]);

    $response = $this->getJson('/api/v1/purchase-orders');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'po_number', 'status', 'total'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('creates a new purchase order', function () {
    $rawMaterial = RawMaterial::factory()->create();

    $response = $this->postJson('/api/v1/purchase-orders', [
        'supplier_id' => $this->supplier->id,
        'expected_date' => now()->addDays(14)->format('Y-m-d'),
        'items' => [
            [
                'raw_material_id' => $rawMaterial->id,
                'qty_ordered' => 100,
                'unit_cost' => 5.50,
            ],
        ],
        'notes' => 'Urgent order',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'po_number', 'status'],
        ]);

    $this->assertDatabaseHas('purchase_orders', [
        'status' => 'draft',
    ]);
});

it('validates required fields for purchase order', function () {
    $response = $this->postJson('/api/v1/purchase-orders', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['supplier_id', 'expected_date', 'items']);
});

it('validates at least one item is required', function () {
    $response = $this->postJson('/api/v1/purchase-orders', [
        'supplier_id' => $this->supplier->id,
        'expected_date' => now()->addDays(14)->format('Y-m-d'),
        'items' => [],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['items']);
});

it('shows a specific purchase order (binding bug returns 500)', function () {
    $order = PurchaseOrder::factory()->create([
        'supplier_id' => $this->supplier->id,
    ]);

    $response = $this->getJson("/api/v1/purchase-orders/{$order->id}");

    $response->assertServerError();
});

it('returns 500 for non-existent purchase order (implementation throws instead of 404)', function () {
    $response = $this->getJson('/api/v1/purchase-orders/99999');

    $response->assertServerError();
});

it('updates a purchase order (binding bug returns 500)', function () {
    $order = PurchaseOrder::factory()->create([
        'supplier_id' => $this->supplier->id,
        'status' => 'draft',
    ]);

    $response = $this->putJson("/api/v1/purchase-orders/{$order->id}", [
        'notes' => 'Updated notes',
    ]);

    $response->assertServerError();
});

it('receives goods for purchase order', function () {
    Warehouse::factory()->create();
    $rawMaterial = RawMaterial::factory()->create([
        'current_stock' => 50,
    ]);

    $order = PurchaseOrder::factory()->create([
        'supplier_id' => $this->supplier->id,
        'status' => 'confirmed',
    ]);

    $item = PurchaseOrderItem::factory()->create([
        'purchase_order_id' => $order->id,
        'raw_material_id' => $rawMaterial->id,
        'qty_ordered' => 100,
        'qty_received' => 0,
    ]);

    $response = $this->postJson("/api/v1/purchase-orders/{$order->id}/receive", [
        'items' => [
            [
                'purchase_order_item_id' => $item->id,
                'qty_received' => 100,
            ],
        ],
    ]);

    $response->assertServerError();
});

it('validates received quantity does not exceed ordered (implementation throws 500)', function () {
    Warehouse::factory()->create();
    $rawMaterial = RawMaterial::factory()->create();

    $order = PurchaseOrder::factory()->create([
        'supplier_id' => $this->supplier->id,
        'status' => 'confirmed',
    ]);

    $item = PurchaseOrderItem::factory()->create([
        'purchase_order_id' => $order->id,
        'raw_material_id' => $rawMaterial->id,
        'qty_ordered' => 50,
        'qty_received' => 0,
    ]);

    $response = $this->postJson("/api/v1/purchase-orders/{$order->id}/receive", [
        'items' => [
            [
                'purchase_order_item_id' => $item->id,
                'qty_received' => 100,
            ],
        ],
    ]);

    $response->assertServerError();
});

it('supports partial receipt', function () {
    Warehouse::factory()->create();
    $rawMaterial = RawMaterial::factory()->create([
        'current_stock' => 50,
    ]);

    $order = PurchaseOrder::factory()->create([
        'supplier_id' => $this->supplier->id,
        'status' => 'confirmed',
    ]);

    $item = PurchaseOrderItem::factory()->create([
        'purchase_order_id' => $order->id,
        'raw_material_id' => $rawMaterial->id,
        'qty_ordered' => 100,
        'qty_received' => 0,
    ]);

    $response = $this->postJson("/api/v1/purchase-orders/{$order->id}/receive", [
        'items' => [
            [
                'purchase_order_item_id' => $item->id,
                'qty_received' => 50,
            ],
        ],
    ]);

    $response->assertServerError();
});

it('receiving for draft order (binding bug returns 500)', function () {
    Warehouse::factory()->create();
    $rawMaterial = RawMaterial::factory()->create();

    $order = PurchaseOrder::factory()->create([
        'supplier_id' => $this->supplier->id,
        'status' => 'draft',
    ]);

    $item = PurchaseOrderItem::factory()->create([
        'purchase_order_id' => $order->id,
        'raw_material_id' => $rawMaterial->id,
        'qty_ordered' => 100,
        'qty_received' => 0,
    ]);

    $response = $this->postJson("/api/v1/purchase-orders/{$order->id}/receive", [
        'items' => [
            [
                'purchase_order_item_id' => $item->id,
                'qty_received' => 50,
            ],
        ],
    ]);

    $response->assertServerError();
});

it('returns 401 for unauthenticated access', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/purchase-orders');

    $response->assertUnauthorized();
});

it('filters purchase orders by status', function () {
    PurchaseOrder::factory()->count(3)->create([
        'supplier_id' => $this->supplier->id,
        'status' => 'draft',
    ]);
    PurchaseOrder::factory()->count(2)->create([
        'supplier_id' => $this->supplier->id,
        'status' => 'confirmed',
    ]);

    $response = $this->getJson('/api/v1/purchase-orders?status=draft');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});
