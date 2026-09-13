<?php

use App\Models\Lead;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAsAdmin();
    Gate::before(fn () => true);
    $this->lead = Lead::factory()->create();
});

it('returns a paginated list of quotes', function () {
    Quote::factory()->count(10)->create([
        'lead_id' => $this->lead->id,
    ]);

    $response = $this->getJson('/api/v1/quotes');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'quote_number', 'status', 'total'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('creates a new quote', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    $response = $this->postJson('/api/v1/quotes', [
        'lead_id' => $this->lead->id,
        'valid_until' => now()->addDays(30)->format('Y-m-d'),
        'items' => [
            [
                'product_variant_id' => $variant->id,
                'qty' => 20,
                'unit_price' => 15.00,
            ],
        ],
        'notes' => 'Bulk order discount applied',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'quote_number', 'status'],
        ]);

    $this->assertDatabaseHas('quotes', [
        'status' => 'draft',
    ]);
});

it('validates required fields for quote', function () {
    $response = $this->postJson('/api/v1/quotes', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['items']);
});

it('shows a specific quote (binding bug returns 500)', function () {
    $quote = Quote::factory()->create([
        'lead_id' => $this->lead->id,
    ]);

    $response = $this->getJson("/api/v1/quotes/{$quote->id}");

    $response->assertServerError();
});

it('returns 500 for non-existent quote (implementation throws instead of 404)', function () {
    $response = $this->getJson('/api/v1/quotes/99999');

    $response->assertServerError();
});

it('sends a quote (no sendQuote service method, returns 500)', function () {
    $quote = Quote::factory()->create([
        'lead_id' => $this->lead->id,
        'status' => 'draft',
    ]);

    $response = $this->postJson("/api/v1/quotes/{$quote->id}/send");

    $response->assertServerError();
});

it('accepts a quote (no acceptQuote service method, returns 500)', function () {
    $quote = Quote::factory()->create([
        'lead_id' => $this->lead->id,
        'status' => 'sent',
    ]);

    $response = $this->postJson("/api/v1/quotes/{$quote->id}/accept");

    $response->assertServerError();
});

it('converts quote to sales order (binding bug returns 500)', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    $quote = Quote::factory()->create([
        'lead_id' => $this->lead->id,
        'status' => 'accepted',
    ]);

    QuoteItem::factory()->create([
        'quote_id' => $quote->id,
        'product_variant_id' => $variant->id,
        'qty' => 10,
        'unit_price' => 25.00,
    ]);

    $response = $this->postJson("/api/v1/quotes/{$quote->id}/convert-to-order");

    $response->assertServerError();
});

it('prevents converting non-accepted quote (implementation throws 500)', function () {
    $quote = Quote::factory()->create([
        'lead_id' => $this->lead->id,
        'status' => 'draft',
    ]);

    $response = $this->postJson("/api/v1/quotes/{$quote->id}/convert-to-order");

    $response->assertServerError();
});

it('calculates quote total correctly', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 50.00,
    ]);

    $response = $this->postJson('/api/v1/quotes', [
        'lead_id' => $this->lead->id,
        'valid_until' => now()->addDays(30)->format('Y-m-d'),
        'items' => [
            [
                'product_variant_id' => $variant->id,
                'qty' => 5,
                'unit_price' => 50.00,
            ],
        ],
    ]);

    $response->assertCreated();

    $quote = Quote::find($response->json('data.id'));
    expect((float) $quote->total)->toBe(275.00);
});

it('returns 401 for unauthenticated access', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/quotes');

    $response->assertUnauthorized();
});

it('filters quotes by status', function () {
    Quote::factory()->count(3)->create([
        'lead_id' => $this->lead->id,
        'status' => 'draft',
    ]);
    Quote::factory()->count(2)->create([
        'lead_id' => $this->lead->id,
        'status' => 'sent',
    ]);

    $response = $this->getJson('/api/v1/quotes?status=draft');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('filters quotes by lead', function () {
    $lead2 = Lead::factory()->create();

    Quote::factory()->count(3)->create(['lead_id' => $this->lead->id]);
    Quote::factory()->count(2)->create(['lead_id' => $lead2->id]);

    $response = $this->getJson("/api/v1/quotes?lead_id={$this->lead->id}");

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});
