<?php

use App\Models\Client;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\NumberingSetting;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\TaxSetting;
use App\Models\TermCondition;
use App\Models\User;

function invoiceFixtures(): array
{
    $project = Project::create([
        'name' => 'Website Redesign',
        'start_date' => '2026-05-22',
        'status' => Project::STATUS_ACTIVE,
    ]);
    $client = Client::create([
        'client_code' => 'LC-2026-0001',
        'sequence_no' => 1,
        'project_id' => $project->id,
        'name' => 'Acme Stores',
        'client_type' => Client::TYPE_LOCAL,
        'email' => 'client@example.com',
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
        'short_name' => 'WEB',
        'long_name' => 'Website Design',
        'default_rate' => 500,
        'status' => Service::STATUS_ACTIVE,
    ]);
    $product = Product::create([
        'product_code' => 'PROD-0001',
        'name' => 'Hosting',
        'description' => 'Annual hosting',
        'unit_price' => 120,
        'status' => Product::STATUS_ACTIVE,
    ]);
    $term = TermCondition::create([
        'name' => 'Standard',
        'content' => 'Payment is due within 14 days.',
        'status' => TermCondition::STATUS_ACTIVE,
    ]);

    return compact('project', 'client', 'currency', 'tax', 'service', 'product', 'term');
}

test('invoices page requires authentication', function () {
    $this->get('/transactions/invoices')->assertRedirect(route('login', absolute: false));
});

test('authenticated users can view invoice workspace', function () {
    $user = User::factory()->create();
    invoiceFixtures();

    $response = $this->actingAs($user)->get('/transactions/invoices');

    $response->assertOk();
    $response->assertSee('Create Invoice');
    $response->assertSee('Invoice List');
    $response->assertSee('Invoice Builder');
    $response->assertSee('Quotation');
});

test('authenticated users can create invoices and list actions', function () {
    $user = User::factory()->create();
    $fixtures = invoiceFixtures();
    NumberingSetting::create(NumberingSetting::defaults());

    $response = $this->actingAs($user)->post('/transactions/invoices', [
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'tax_setting_id' => $fixtures['tax']->id,
        'term_condition_id' => $fixtures['term']->id,
        'invoice_date' => '2026-05-22',
        'due_date' => '2026-06-05',
        'item_type' => ['service', 'product'],
        'item_source_id' => [$fixtures['service']->id, $fixtures['product']->id],
        'quantity' => [2, 1],
        'rate' => [500, 120],
    ]);

    $response->assertRedirect(route('transactions.invoices.index', absolute: false));
    $this->assertDatabaseHas('invoices', [
        'invoice_no' => 'INV-2026-0001',
        'subtotal' => 1120,
        'tax_amount' => 112,
        'total' => 1232,
        'balance_due' => 1232,
        'status' => Invoice::STATUS_DRAFT,
    ]);
    $this->assertDatabaseCount('invoice_items', 2);

    $listResponse = $this->actingAs($user)->get('/transactions/invoices');
    $listResponse->assertSee('INV-2026-0001');
    $listResponse->assertSee('View');
    $listResponse->assertSee('Edit');
    $listResponse->assertSee('Duplicate');
    $listResponse->assertSee('Delete');
    $listResponse->assertSee('PDF');
    $listResponse->assertSee('Send');
    $listResponse->assertSee('Payment Status');
});

test('approved quotation can be loaded into invoice form and saved as invoice', function () {
    $user = User::factory()->create();
    $fixtures = invoiceFixtures();
    NumberingSetting::create(NumberingSetting::defaults());

    $quotation = Quotation::create([
        'quotation_no' => 'QUO-2026-0007',
        'sequence_no' => 7,
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'tax_setting_id' => $fixtures['tax']->id,
        'term_condition_id' => $fixtures['term']->id,
        'quotation_date' => '2026-05-22',
        'validity_date' => '2026-06-05',
        'subtotal' => 1000,
        'tax_rate_percent' => 10,
        'tax_amount' => 100,
        'total' => 1100,
        'status' => Quotation::STATUS_APPROVED,
    ]);
    $quotation->items()->create([
        'item_type' => 'service',
        'item_source_id' => $fixtures['service']->id,
        'item_name' => 'Website Design',
        'quantity' => 2,
        'rate' => 500,
        'line_total' => 1000,
    ]);
    $quotation->items()->create([
        'item_type' => 'product',
        'item_source_id' => null,
        'item_name' => 'Quoted Laptop',
        'quantity' => 1,
        'rate' => 900,
        'line_total' => 900,
    ]);

    $loadResponse = $this->actingAs($user)->get(route('transactions.invoices.index', ['quotation_id' => $quotation->id], false));
    $loadResponse->assertOk();
    $loadResponse->assertSee('Loaded approved quotation');
    $loadResponse->assertSee('QUO-2026-0007');
    $loadResponse->assertSee('name="source_quotation_id"', false);
    $loadResponse->assertSee('\u0022label\u0022:\u0022Website Design\u0022', false);
    $loadResponse->assertSee('\u0022label\u0022:\u0022Quoted Laptop\u0022', false);
    $loadResponse->assertSee('\u0022itemId\u0022:\u0022quoted-', false);
    $loadResponse->assertSee('\u0022quantity\u0022:2', false);
    $loadResponse->assertSee('\u0022rate\u0022:500', false);

    $saveResponse = $this->actingAs($user)->post('/transactions/invoices', [
        'source_quotation_id' => $quotation->id,
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'tax_setting_id' => $fixtures['tax']->id,
        'term_condition_id' => $fixtures['term']->id,
        'invoice_date' => '2026-05-22',
        'due_date' => '2026-06-05',
        'item_type' => ['service', 'product'],
        'item_source_id' => [$fixtures['service']->id, 'quoted-custom-product'],
        'item_name' => ['Website Design', 'Quoted Laptop'],
        'quantity' => [2, 1],
        'rate' => [500, 900],
    ]);

    $saveResponse->assertRedirect(route('transactions.invoices.index', absolute: false));
    $this->assertDatabaseHas('invoices', [
        'invoice_no' => 'INV-2026-0001',
        'source_quotation_id' => $quotation->id,
        'total' => 2090,
    ]);
    $this->assertDatabaseHas('invoice_items', [
        'item_name' => 'Quoted Laptop',
        'item_source_id' => null,
        'line_total' => 900,
    ]);
    expect($quotation->refresh()->status)->toBe(Quotation::STATUS_CONVERTED);

    $pickerResponse = $this->actingAs($user)->get(route('transactions.invoices.index', absolute: false));
    $pickerResponse->assertDontSee('QUO-2026-0007');
});

test('deleting converted invoice releases quotation for deletion or reuse', function () {
    $user = User::factory()->create();
    $fixtures = invoiceFixtures();

    $quotation = Quotation::create([
        'quotation_no' => 'QUO-2026-0008',
        'sequence_no' => 8,
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'quotation_date' => '2026-05-22',
        'subtotal' => 500,
        'tax_rate_percent' => 0,
        'tax_amount' => 0,
        'total' => 500,
        'status' => Quotation::STATUS_CONVERTED,
        'converted_at' => now(),
    ]);
    $invoice = Invoice::create([
        'invoice_no' => 'INV-2026-0010',
        'sequence_no' => 10,
        'source_quotation_id' => $quotation->id,
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'invoice_date' => '2026-05-22',
        'subtotal' => 500,
        'tax_rate_percent' => 0,
        'tax_amount' => 0,
        'total' => 500,
        'balance_due' => 500,
        'status' => Invoice::STATUS_DRAFT,
    ]);

    $this->actingAs($user)
        ->delete(route('transactions.invoices.destroy', $invoice, absolute: false))
        ->assertRedirect(route('transactions.invoices.index', absolute: false));

    expect($quotation->refresh()->status)->toBe(Quotation::STATUS_APPROVED)
        ->and($quotation->converted_at)->toBeNull();
});

test('authenticated users can view invoice payment status and download pdf', function () {
    $user = User::factory()->create();
    $fixtures = invoiceFixtures();

    $invoice = Invoice::create([
        'invoice_no' => 'INV-2026-0009',
        'sequence_no' => 9,
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'invoice_date' => '2026-05-22',
        'due_date' => '2026-06-05',
        'subtotal' => 500,
        'tax_rate_percent' => 0,
        'tax_amount' => 0,
        'total' => 500,
        'balance_due' => 500,
        'status' => Invoice::STATUS_DRAFT,
    ]);
    $invoice->items()->create([
        'item_type' => 'service',
        'item_source_id' => $fixtures['service']->id,
        'item_name' => 'Website Design',
        'quantity' => 1,
        'rate' => 500,
        'line_total' => 500,
    ]);

    $this->actingAs($user)
        ->get(route('transactions.invoices.payment-status', $invoice, absolute: false))
        ->assertOk()
        ->assertSee('Payment Posting');

    $this->actingAs($user)
        ->get(route('transactions.invoices.pdf', $invoice, absolute: false))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
