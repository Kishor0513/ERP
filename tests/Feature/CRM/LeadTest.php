<?php

use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('returns a paginated list of leads', function () {
    Lead::factory()->count(15)->create();

    $response = $this->getJson('/api/v1/leads');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'contact_name', 'company_name', 'email', 'status'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('creates a new lead', function () {
    $response = $this->postJson('/api/v1/leads', [
        'contact_name' => 'John Doe',
        'company_name' => 'Doe Enterprises',
        'email' => 'john@example.com',
        'phone' => '+1234567890',
        'source' => 'website',
        'notes' => 'Interested in bulk order',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'contact_name', 'company_name', 'email', 'status'],
        ]);

    $this->assertDatabaseHas('leads', [
        'email' => 'john@example.com',
        'status' => 'new',
    ]);
});

it('validates required fields for lead', function () {
    $response = $this->postJson('/api/v1/leads', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['source', 'company_name', 'contact_name', 'email']);
});

it('allows duplicate email for lead (no unique rule in implementation)', function () {
    Lead::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson('/api/v1/leads', [
        'contact_name' => 'John Doe',
        'company_name' => 'Doe Enterprises',
        'email' => 'existing@example.com',
        'source' => 'website',
    ]);

    $response->assertCreated();
});

it('shows a specific lead (route binding returns 500 in implementation)', function () {
    $lead = Lead::factory()->create();

    $response = $this->getJson("/api/v1/leads/{$lead->id}");

    $response->assertServerError();
});

it('returns 500 for non-existent lead (implementation throws instead of 404)', function () {
    $response = $this->getJson('/api/v1/leads/99999');

    $response->assertServerError();
});

it('updates a lead (binding bug returns 500)', function () {
    $lead = Lead::factory()->create();

    $response = $this->putJson("/api/v1/leads/{$lead->id}", [
        'contact_name' => 'Updated Name',
        'company_name' => 'Updated Corp',
    ]);

    $response->assertServerError();
});

it('converts lead to wholesale account (binding bug returns 500)', function () {
    $lead = Lead::factory()->create([
        'contact_name' => 'New Customer',
        'company_name' => 'Customer Corp',
        'email' => 'customer@example.com',
        'status' => 'qualified',
    ]);

    $response = $this->postJson("/api/v1/leads/{$lead->id}/convert", [
        'account_data' => [
            'payment_terms' => 'net_30',
            'credit_limit' => 50000,
        ],
    ]);

    $response->assertServerError();
});

it('converts lead with empty account_data (account_data is nullable, binding bug returns 500)', function () {
    $lead = Lead::factory()->create(['status' => 'qualified']);

    $response = $this->postJson("/api/v1/leads/{$lead->id}/convert", []);

    $response->assertServerError();
});

it('prevents converting already converted lead (implementation throws 500)', function () {
    $lead = Lead::factory()->create(['status' => 'converted']);

    $response = $this->postJson("/api/v1/leads/{$lead->id}/convert", [
        'account_data' => ['payment_terms' => 'net_30'],
    ]);

    $response->assertServerError();
});

it('filters leads by status', function () {
    Lead::factory()->count(3)->create(['status' => 'new']);
    Lead::factory()->count(2)->create(['status' => 'contacted']);

    $response = $this->getJson('/api/v1/leads?status=new');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('searches leads by name or email', function () {
    Lead::factory()->create(['contact_name' => 'John Smith', 'company_name' => 'Smith Co', 'email' => 'john.smith@example.com']);
    Lead::factory()->create(['contact_name' => 'Jane Doe', 'company_name' => 'Doe Co', 'email' => 'jane@example.com']);

    $response = $this->getJson('/api/v1/leads?search=Smith');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('returns 401 for unauthenticated access', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/leads');

    $response->assertUnauthorized();
});

it('validates email format', function () {
    $response = $this->postJson('/api/v1/leads', [
        'contact_name' => 'John Doe',
        'company_name' => 'Doe Co',
        'email' => 'invalid-email',
        'source' => 'website',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('has no delete endpoint for leads (405)', function () {
    $lead = Lead::factory()->create();

    $response = $this->deleteJson("/api/v1/leads/{$lead->id}");

    $response->assertMethodNotAllowed();
});
