<?php

use App\Models\Client;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\User;

function dashboardFixtures(): void
{
    $project = Project::create([
        'name' => 'Dashboard Project',
        'start_date' => '2026-05-01',
        'status' => Project::STATUS_ACTIVE,
    ]);

    $client = Client::create([
        'client_code' => 'LC-2026-0201',
        'sequence_no' => 201,
        'project_id' => $project->id,
        'name' => 'Dashboard Client',
        'client_type' => Client::TYPE_LOCAL,
        'email' => 'dashboard@example.com',
        'phone' => '+2348012345678',
        'address' => 'Lagos',
        'tax_applicable' => false,
        'tax_percent' => 0,
        'status' => Client::STATUS_ACTIVE,
    ]);

    $currency = Currency::create([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => '$',
        'exchange_rate' => 1,
        'is_default' => true,
        'status' => Currency::STATUS_ACTIVE,
    ]);

    $overdue = Invoice::create([
        'invoice_no' => 'INV-2026-0201',
        'sequence_no' => 201,
        'client_id' => $client->id,
        'project_id' => $project->id,
        'currency_id' => $currency->id,
        'invoice_date' => now()->subDays(20)->toDateString(),
        'due_date' => now()->subDay()->toDateString(),
        'subtotal' => 1000,
        'tax_amount' => 0,
        'total' => 1000,
        'amount_paid' => 200,
        'balance_due' => 800,
        'status' => Invoice::STATUS_PARTIALLY_PAID,
    ]);

    InvoicePayment::create([
        'invoice_id' => $overdue->id,
        'payment_date' => now()->toDateString(),
        'amount' => 200,
        'payment_method' => 'bank_transfer',
    ]);

    Invoice::create([
        'invoice_no' => 'INV-2026-0202',
        'sequence_no' => 202,
        'client_id' => $client->id,
        'project_id' => $project->id,
        'currency_id' => $currency->id,
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(3)->toDateString(),
        'subtotal' => 500,
        'tax_amount' => 0,
        'total' => 500,
        'amount_paid' => 0,
        'balance_due' => 500,
        'status' => Invoice::STATUS_SENT,
    ]);

    Quotation::create([
        'quotation_no' => 'QUO-2026-0201',
        'sequence_no' => 201,
        'client_id' => $client->id,
        'project_id' => $project->id,
        'currency_id' => $currency->id,
        'quotation_date' => now()->toDateString(),
        'validity_date' => now()->addDays(2)->toDateString(),
        'subtotal' => 900,
        'tax_amount' => 0,
        'total' => 900,
        'status' => Quotation::STATUS_SENT,
    ]);
}

test('dashboard requires authentication', function () {
    $this->get('/dashboard')->assertRedirect(route('login', absolute: false));
});

test('dashboard shows live decision analytics', function () {
    $user = User::factory()->create();
    dashboardFixtures();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('Total revenue');
    $response->assertSee('Outstanding');
    $response->assertSee('Monthly collections');
    $response->assertSee('Quotations this month');
    $response->assertSee('Conversion %');
    $response->assertSee('Active clients');
    $response->assertSee('Active projects');
    $response->assertSee('Overdue invoices');
    $response->assertSee('Invoices due soon');
    $response->assertSee('Quotations expiring');
    $response->assertSee('INV-2026-0201');
    $response->assertSee('QUO-2026-0201');
});
