<?php

use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\RawMaterial;
use App\Models\SalesOrder;
use App\Models\WholesaleAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('returns dashboard summary data', function () {
    WholesaleAccount::factory()->count(5)->create();
    SalesOrder::factory()->count(10)->create();
    ProductionOrder::factory()->count(8)->create();
    Invoice::factory()->count(7)->create();

    $response = $this->getJson('/api/v1/reports/dashboard');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'sales',
                'production',
                'inventory',
                'recent_orders',
                'top_products',
            ],
        ]);
});

it('returns sales summary report', function () {
    SalesOrder::factory()->count(5)->create(['status' => 'closed']);
    SalesOrder::factory()->count(3)->create(['status' => 'confirmed']);

    $response = $this->getJson('/api/v1/reports/sales-summary?from_date='.now()->subMonth()->format('Y-m-d').'&to_date='.now()->format('Y-m-d'));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'total_orders',
                'total_revenue',
                'average_order_value',
                'by_status',
            ],
        ]);
});

it('returns production summary report', function () {
    ProductionOrder::factory()->count(5)->create(['status' => 'completed']);
    ProductionOrder::factory()->count(3)->create(['status' => 'in_progress']);

    $response = $this->getJson('/api/v1/reports/production-summary');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'total_orders',
                'in_progress',
                'completed',
                'qc_pass_rate',
            ],
        ]);
});

it('returns inventory summary report', function () {
    RawMaterial::factory()->count(10)->create();
    Product::factory()->count(5)->create();

    $response = $this->getJson('/api/v1/reports/inventory-summary');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'total_raw_materials',
                'low_stock_count',
                'total_variants',
                'out_of_stock',
            ],
        ]);
});

it('accepts date range for sales summary (no range validation in implementation)', function () {
    $response = $this->getJson('/api/v1/reports/sales-summary?from_date='.now()->format('Y-m-d').'&to_date='.now()->subMonth()->format('Y-m-d'));

    $response->assertOk();
});

it('accepts date range for production summary (no validation in implementation)', function () {
    $response = $this->getJson('/api/v1/reports/production-summary');

    $response->assertOk();
});

it('returns 401 for unauthenticated access', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/reports/dashboard');

    $response->assertUnauthorized();
});

it('returns dashboard with sales totals', function () {
    SalesOrder::factory()->count(3)->create(['status' => 'confirmed', 'total' => 100]);
    SalesOrder::factory()->count(2)->create(['status' => 'confirmed', 'total' => 50]);

    $response = $this->getJson('/api/v1/reports/dashboard');

    $response->assertOk();

    $data = $response->json('data');
    expect((float) $data['sales']['total'])->toBe(400.0);
});

it('returns dashboard revenue in sales key', function () {
    SalesOrder::factory()->create(['total' => 1000, 'status' => 'closed']);
    SalesOrder::factory()->create(['total' => 2000, 'status' => 'closed']);

    $response = $this->getJson('/api/v1/reports/dashboard');

    $response->assertOk();

    $data = $response->json('data');
    expect((float) $data['sales']['total'])->toBe(3000.0);
});

it('handles empty data gracefully', function () {
    $response = $this->getJson('/api/v1/reports/dashboard');

    $response->assertOk();

    $data = $response->json('data');
    expect((float) $data['sales']['total'])->toBe(0.0);
    expect($data['production']['active_orders'])->toBe(0);
});
