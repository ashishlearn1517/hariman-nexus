<?php

use App\Models\Client;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Vendor;

function reportFixtures(): array
{
    $project = Project::create([
        'name' => 'Report Project',
        'start_date' => '2026-05-01',
        'status' => Project::STATUS_ACTIVE,
    ]);

    $client = Client::create([
        'client_code' => 'LC-2026-0101',
        'sequence_no' => 101,
        'project_id' => $project->id,
        'name' => 'Report Client',
        'client_type' => Client::TYPE_LOCAL,
        'email' => 'report@example.com',
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

    $invoice = Invoice::create([
        'invoice_no' => 'INV-2026-0101',
        'sequence_no' => 101,
        'client_id' => $client->id,
        'project_id' => $project->id,
        'currency_id' => $currency->id,
        'invoice_date' => '2026-05-10',
        'due_date' => '2026-05-20',
        'subtotal' => 1000,
        'tax_amount' => 0,
        'total' => 1000,
        'amount_paid' => 300,
        'balance_due' => 700,
        'status' => Invoice::STATUS_PARTIALLY_PAID,
    ]);

    InvoicePayment::create([
        'invoice_id' => $invoice->id,
        'payment_date' => '2026-05-12',
        'amount' => 300,
        'payment_method' => 'bank_transfer',
    ]);

    $expenseCategory = ExpenseCategory::create([
        'category_code' => 'EXC001',
        'category_name' => 'Travel',
        'status' => ExpenseCategory::STATUS_ACTIVE,
    ]);
    $vendor = Vendor::create([
        'vendor_code' => 'VEN001',
        'vendor_name' => 'Report Vendor',
        'status' => Vendor::STATUS_ACTIVE,
    ]);

    Expense::create([
        'expense_no' => 'EXP-2026-0101',
        'sequence_no' => 101,
        'expense_date' => '2026-05-15',
        'expense_category_id' => $expenseCategory->id,
        'project_id' => $project->id,
        'vendor_id' => $vendor->id,
        'vendor_name' => $vendor->vendor_name,
        'amount' => 400,
        'tax_amount' => 40,
        'total_amount' => 440,
        'payment_method' => 'online_payment',
        'status' => Expense::STATUS_PAID,
    ]);

    Quotation::create([
        'quotation_no' => 'QUO-2026-0101',
        'sequence_no' => 101,
        'client_id' => $client->id,
        'project_id' => $project->id,
        'currency_id' => $currency->id,
        'quotation_date' => '2026-05-01',
        'validity_date' => '2026-06-01',
        'subtotal' => 1000,
        'tax_amount' => 0,
        'total' => 1000,
        'status' => Quotation::STATUS_CONVERTED,
        'converted_at' => '2026-05-10 00:00:00',
    ]);

    return compact('project', 'client', 'currency', 'invoice');
}

test('reports page requires authentication', function () {
    $this->get('/reports')->assertRedirect(route('login', absolute: false));
});

test('users with report permission can view reporting workspace', function () {
    $user = User::factory()->create();
    reportFixtures();

    $response = $this->actingAs($user)->get('/reports?date_from=2026-05-01&date_to=2026-05-31');

    $response->assertOk();
    $response->assertSee('Revenue Report');
    $response->assertSee('Outstanding Invoice Report');
    $response->assertSee('Client Statement');
    $response->assertSee('Payment Collection Report');
    $response->assertSee('Quotation Conversion Report');
    $response->assertSee('Monthly Expense Summary');
    $response->assertSee('Expense By Category');
    $response->assertSee('Profit & Loss Report');
    $response->assertSee('Project Profitability');
    $response->assertSee('Report Project');
    $response->assertSee('Travel');
    $response->assertSee('INV-2026-0101');
});

test('reports can be exported as csv', function () {
    $user = User::factory()->create();
    reportFixtures();

    $response = $this->actingAs($user)->get('/reports?date_from=2026-05-01&date_to=2026-05-31&export=revenue');

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('content-disposition'))->toContain('revenue-report.csv');
});

test('reports can be exported as pdf', function () {
    $user = User::factory()->create();
    reportFixtures();

    $response = $this->actingAs($user)->get('/reports?date_from=2026-05-01&date_to=2026-05-31&export=pdf');

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    expect($response->headers->get('content-disposition'))->toContain('hariman-nexus-reports.pdf');
});

test('reports can be exported as excel', function () {
    $user = User::factory()->create();
    reportFixtures();

    $response = $this->actingAs($user)->get('/reports?date_from=2026-05-01&date_to=2026-05-31&export=revenue_xlsx');

    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('revenue-report.xlsx');
});

test('expense report can be exported as csv and excel', function () {
    $user = User::factory()->create();
    reportFixtures();

    $csv = $this->actingAs($user)->get('/reports?date_from=2026-05-01&date_to=2026-05-31&export=expenses');
    $xlsx = $this->actingAs($user)->get('/reports?date_from=2026-05-01&date_to=2026-05-31&export=expenses_xlsx');

    $csv->assertOk();
    expect($csv->headers->get('content-disposition'))->toContain('expenses-report.csv');

    $xlsx->assertOk();
    expect($xlsx->headers->get('content-disposition'))->toContain('expenses-report.xlsx');
});

test('profit and project reports can be exported as csv and excel', function () {
    $user = User::factory()->create();
    reportFixtures();

    $profitCsv = $this->actingAs($user)->get('/reports?date_from=2026-05-01&date_to=2026-05-31&export=profit_loss');
    $profitXlsx = $this->actingAs($user)->get('/reports?date_from=2026-05-01&date_to=2026-05-31&export=profit_loss_xlsx');
    $projectCsv = $this->actingAs($user)->get('/reports?date_from=2026-05-01&date_to=2026-05-31&export=project_profitability');
    $projectXlsx = $this->actingAs($user)->get('/reports?date_from=2026-05-01&date_to=2026-05-31&export=project_profitability_xlsx');

    $profitCsv->assertOk();
    expect($profitCsv->headers->get('content-disposition'))->toContain('profit_loss-report.csv');

    $profitXlsx->assertOk();
    expect($profitXlsx->headers->get('content-disposition'))->toContain('profit_loss-report.xlsx');

    $projectCsv->assertOk();
    expect($projectCsv->headers->get('content-disposition'))->toContain('project_profitability-report.csv');

    $projectXlsx->assertOk();
    expect($projectXlsx->headers->get('content-disposition'))->toContain('project_profitability-report.xlsx');
});
