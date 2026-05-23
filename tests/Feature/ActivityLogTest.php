<?php

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\NumberingSetting;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Models\TaxSetting;
use App\Models\TermCondition;
use App\Models\User;

function activityLogFixtures(): array
{
    $project = Project::create([
        'name' => 'Audit Project',
        'start_date' => '2026-05-22',
        'status' => Project::STATUS_ACTIVE,
    ]);

    $client = Client::create([
        'client_code' => 'LC-2026-0099',
        'sequence_no' => 99,
        'project_id' => $project->id,
        'name' => 'Audit Client',
        'client_type' => Client::TYPE_LOCAL,
        'email' => 'audit@example.com',
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

    $tax = TaxSetting::create([
        'name' => 'VAT',
        'rate_percent' => 10,
        'status' => TaxSetting::STATUS_ACTIVE,
    ]);

    $service = Service::create([
        'short_name' => 'AUD',
        'long_name' => 'Audit Service',
        'default_rate' => 500,
        'status' => Service::STATUS_ACTIVE,
    ]);

    $product = Product::create([
        'product_code' => 'PROD-0099',
        'name' => 'Audit Product',
        'unit_price' => 120,
        'status' => Product::STATUS_ACTIVE,
    ]);

    $term = TermCondition::create([
        'name' => 'Audit Term',
        'content' => 'Audit payment terms.',
        'status' => TermCondition::STATUS_ACTIVE,
    ]);

    NumberingSetting::create(NumberingSetting::defaults());

    return compact('project', 'client', 'currency', 'tax', 'service', 'product', 'term');
}

test('successful login creates an activity log entry', function () {
    $user = User::factory()->create([
        'email' => 'audit-login@example.com',
    ]);

    $this->post('/login', [
        'email' => 'audit-login@example.com',
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $user->id,
        'module' => 'auth',
        'action' => 'login',
    ]);
});

test('invoice and payment actions create activity log entries', function () {
    $user = User::factory()->create();
    $fixtures = activityLogFixtures();

    $this->actingAs($user)->post('/transactions/invoices', [
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'tax_setting_id' => $fixtures['tax']->id,
        'term_condition_id' => $fixtures['term']->id,
        'invoice_date' => '2026-05-22',
        'due_date' => '2026-06-05',
        'item_type' => ['service'],
        'item_source_id' => [$fixtures['service']->id],
        'quantity' => [1],
        'rate' => [500],
    ])->assertRedirect(route('transactions.invoices.index', absolute: false));

    $invoice = Invoice::query()->firstOrFail();

    $this->actingAs($user)->post(route('transactions.invoices.payments.store', $invoice, false), [
        'payment_date' => '2026-05-22',
        'amount' => 100,
        'payment_method' => 'bank_transfer',
    ])->assertRedirect(route('transactions.invoices.payment-status', $invoice, false));

    expect(ActivityLog::query()->where('module', 'invoices')->where('action', 'created')->exists())->toBeTrue();
    expect(ActivityLog::query()->where('module', 'payments')->where('action', 'created')->exists())->toBeTrue();
    expect(ActivityLog::query()->where('module', 'invoices')->where('action', 'status_changed')->exists())->toBeTrue();
});

test('activity log viewer is available to permitted users', function () {
    $user = User::factory()->create();

    ActivityLog::create([
        'user_id' => $user->id,
        'module' => 'quotations',
        'action' => 'approved',
        'description' => 'Ashish approved Quotation QUO-2026-0002.',
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/activity-logs');

    $response->assertOk();
    $response->assertSee('Activity Logs');
    $response->assertSee('Ashish approved Quotation QUO-2026-0002.');
});
