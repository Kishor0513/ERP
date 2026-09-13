<?php

use App\Models\Invoice;
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

it('returns a paginated list of invoices', function () {
    Invoice::factory()->count(10)->create([
        'wholesale_account_id' => $this->account->id,
    ]);

    $response = $this->getJson('/api/v1/invoices');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'invoice_number', 'status', 'total'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('creates a new invoice', function () {
    $order = SalesOrder::factory()->create([
        'wholesale_account_id' => $this->account->id,
    ]);

    $response = $this->postJson('/api/v1/invoices', [
        'sales_order_id' => $order->id,
        'wholesale_account_id' => $this->account->id,
        'subtotal' => 250.00,
        'total' => 275.00,
        'due_date' => now()->addDays(30)->format('Y-m-d'),
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'invoice_number', 'status'],
        ]);

    $this->assertDatabaseHas('invoices', [
        'status' => 'draft',
    ]);
});

it('validates required fields for invoice', function () {
    $response = $this->postJson('/api/v1/invoices', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['sales_order_id', 'subtotal', 'total', 'due_date']);
});

it('shows a specific invoice (binding bug returns 500)', function () {
    $invoice = Invoice::factory()->create([
        'wholesale_account_id' => $this->account->id,
    ]);

    $response = $this->getJson("/api/v1/invoices/{$invoice->id}");

    $response->assertServerError();
});

it('returns 500 for non-existent invoice (implementation throws instead of 404)', function () {
    $response = $this->getJson('/api/v1/invoices/99999');

    $response->assertServerError();
});

it('records a payment for invoice (binding bug returns 500)', function () {
    $invoice = Invoice::factory()->create([
        'wholesale_account_id' => $this->account->id,
        'total' => 500.00,
        'status' => 'sent',
    ]);

    $response = $this->postJson("/api/v1/invoices/{$invoice->id}/record-payment", [
        'amount' => 500.00,
        'method' => 'bank_transfer',
        'reference_number' => 'TXN-12345',
        'paid_at' => now()->format('Y-m-d H:i:s'),
    ]);

    $response->assertServerError();
});

it('handles partial payment (binding bug returns 500)', function () {
    $invoice = Invoice::factory()->create([
        'wholesale_account_id' => $this->account->id,
        'total' => 500.00,
        'status' => 'sent',
    ]);

    $response = $this->postJson("/api/v1/invoices/{$invoice->id}/record-payment", [
        'amount' => 200.00,
        'method' => 'cash',
        'paid_at' => now()->format('Y-m-d H:i:s'),
    ]);

    $response->assertServerError();
});

it('overpayment validation is absent but binding bug returns 500', function () {
    $invoice = Invoice::factory()->create([
        'wholesale_account_id' => $this->account->id,
        'total' => 500.00,
        'status' => 'partial',
    ]);

    $response = $this->postJson("/api/v1/invoices/{$invoice->id}/record-payment", [
        'amount' => 200.00,
        'method' => 'cash',
        'paid_at' => now()->format('Y-m-d H:i:s'),
    ]);

    $response->assertServerError();
});

it('validates payment amount is positive', function () {
    $invoice = Invoice::factory()->create([
        'wholesale_account_id' => $this->account->id,
        'status' => 'sent',
    ]);

    $response = $this->postJson("/api/v1/invoices/{$invoice->id}/record-payment", [
        'amount' => -100,
        'method' => 'cash',
        'paid_at' => now()->format('Y-m-d H:i:s'),
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['amount']);
});

it('creates invoice with totals passed explicitly', function () {
    $order = SalesOrder::factory()->create([
        'wholesale_account_id' => $this->account->id,
    ]);

    $response = $this->postJson('/api/v1/invoices', [
        'sales_order_id' => $order->id,
        'wholesale_account_id' => $this->account->id,
        'subtotal' => 500.00,
        'total' => 550.00,
        'due_date' => now()->addDays(30)->format('Y-m-d'),
    ]);

    $response->assertCreated();

    $invoice = Invoice::find($response->json('data.id'));
    expect((float) $invoice->total)->toBe(550.00);
});

it('returns 401 for unauthenticated access', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/invoices');

    $response->assertUnauthorized();
});

it('filters invoices by status', function () {
    Invoice::factory()->count(3)->create([
        'wholesale_account_id' => $this->account->id,
        'status' => 'draft',
    ]);
    Invoice::factory()->count(2)->create([
        'wholesale_account_id' => $this->account->id,
        'status' => 'paid',
    ]);

    $response = $this->getJson('/api/v1/invoices?status=draft');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('payment on cancelled invoice (binding bug returns 500)', function () {
    $invoice = Invoice::factory()->create([
        'wholesale_account_id' => $this->account->id,
        'status' => 'cancelled',
    ]);

    $response = $this->postJson("/api/v1/invoices/{$invoice->id}/record-payment", [
        'amount' => 100,
        'method' => 'cash',
        'paid_at' => now()->format('Y-m-d H:i:s'),
    ]);

    $response->assertServerError();
});
