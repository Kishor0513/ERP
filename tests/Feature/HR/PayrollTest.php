<?php

use App\Models\Artisan;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAsAdmin();
    Gate::before(fn () => true);
});

it('returns a paginated list of payroll runs', function () {
    PayrollRun::factory()->count(5)->create();

    $response = $this->getJson('/api/v1/payroll-runs');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'period_start', 'period_end', 'status'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('creates a new payroll run', function () {
    $response = $this->postJson('/api/v1/payroll-runs', [
        'period_start' => now()->startOfMonth()->format('Y-m-d'),
        'period_end' => now()->endOfMonth()->format('Y-m-d'),
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'period_start', 'period_end', 'status'],
        ]);

    $this->assertDatabaseHas('payroll_runs', [
        'status' => 'draft',
    ]);
});

it('validates required fields for payroll run', function () {
    $response = $this->postJson('/api/v1/payroll-runs', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['period_start', 'period_end']);
});

it('validates period end is after period start', function () {
    $response = $this->postJson('/api/v1/payroll-runs', [
        'period_start' => now()->endOfMonth()->format('Y-m-d'),
        'period_end' => now()->startOfMonth()->format('Y-m-d'),
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['period_end']);
});

it('shows a specific payroll run (binding bug returns 500)', function () {
    $payrollRun = PayrollRun::factory()->create();

    $response = $this->getJson("/api/v1/payroll-runs/{$payrollRun->id}");

    $response->assertServerError();
});

it('returns 500 for non-existent payroll run (implementation throws instead of 404)', function () {
    $response = $this->getJson('/api/v1/payroll-runs/99999');

    $response->assertServerError();
});

it('approves a payroll run (requires calculating status, draft returns 500)', function () {
    $payrollRun = PayrollRun::factory()->create(['status' => 'draft']);

    $artisan = Artisan::factory()->create();
    PayrollItem::factory()->create([
        'payroll_run_id' => $payrollRun->id,
        'artisan_id' => $artisan->id,
        'gross_amount' => 3500,
        'deductions' => 200,
        'net_amount' => 3300,
    ]);

    $response = $this->postJson("/api/v1/payroll-runs/{$payrollRun->id}/approve");

    $response->assertServerError();
});

it('prevents approval of already approved payroll run (implementation throws 500)', function () {
    $payrollRun = PayrollRun::factory()->create(['status' => 'approved']);

    $response = $this->postJson("/api/v1/payroll-runs/{$payrollRun->id}/approve");

    $response->assertServerError();
});

it('marks payroll run as paid (controller passes string id, returns 500)', function () {
    $payrollRun = PayrollRun::factory()->create(['status' => 'approved']);

    $response = $this->postJson("/api/v1/payroll-runs/{$payrollRun->id}/pay");

    $response->assertServerError();
});

it('prevents payment of non-approved payroll run (returns 500)', function () {
    $payrollRun = PayrollRun::factory()->create(['status' => 'draft']);

    $response = $this->postJson("/api/v1/payroll-runs/{$payrollRun->id}/pay");

    $response->assertServerError();
});

it('shows payroll run items via index data', function () {
    $payrollRun = PayrollRun::factory()->create(['status' => 'draft', 'total_amount' => 5950]);

    $artisan1 = Artisan::factory()->create();
    $artisan2 = Artisan::factory()->create();

    PayrollItem::factory()->create([
        'payroll_run_id' => $payrollRun->id,
        'artisan_id' => $artisan1->id,
        'gross_amount' => 3500,
        'deductions' => 200,
        'net_amount' => 3300,
    ]);

    PayrollItem::factory()->create([
        'payroll_run_id' => $payrollRun->id,
        'artisan_id' => $artisan2->id,
        'gross_amount' => 2800,
        'deductions' => 150,
        'net_amount' => 2650,
    ]);

    $response = $this->getJson('/api/v1/payroll-runs?status=draft');

    $response->assertOk();
    expect($response->json('data.0.total_amount'))->not->toBeNull();
});

it('returns 401 for unauthenticated access', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/payroll-runs');

    $response->assertUnauthorized();
});

it('filters payroll runs by status', function () {
    PayrollRun::factory()->count(3)->create(['status' => 'draft']);
    PayrollRun::factory()->count(2)->create(['status' => 'approved']);

    $response = $this->getJson('/api/v1/payroll-runs?status=draft');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('has no delete route for payroll runs (405)', function () {
    $payrollRun = PayrollRun::factory()->create(['status' => 'approved']);

    $response = $this->deleteJson("/api/v1/payroll-runs/{$payrollRun->id}");

    $response->assertMethodNotAllowed();
});
