<?php

use App\Models\Client;
use App\Models\CompanySetting;
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

function quotationFixtures(): array
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
        'content' => '50% advance payment.',
        'status' => TermCondition::STATUS_ACTIVE,
    ]);

    return compact('project', 'client', 'currency', 'tax', 'service', 'product', 'term');
}

test('quotations page requires authentication', function () {
    $response = $this->get('/transactions/quotations');

    $response->assertRedirect(route('login', absolute: false));
});

test('authenticated users can view quotation workspace', function () {
    $user = User::factory()->create();
    quotationFixtures();

    $response = $this->actingAs($user)->get('/transactions/quotations');

    $response->assertOk();
    $response->assertSee('Create Quotation');
    $response->assertSee('Quotation List');
    $response->assertSee('Offer Builder');
    $response->assertSee('Search item...');
});

test('quotation requires core fields and at least one item', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/transactions/quotations', []);

    $response->assertSessionHasErrors([
        'client_id',
        'project_id',
        'currency_id',
        'quotation_date',
        'item_type',
    ]);
});

test('authenticated users can create quotations and list them on same page', function () {
    $user = User::factory()->create();
    $fixtures = quotationFixtures();
    NumberingSetting::create(NumberingSetting::defaults());

    $response = $this->actingAs($user)->post('/transactions/quotations', [
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'tax_setting_id' => $fixtures['tax']->id,
        'term_condition_id' => $fixtures['term']->id,
        'quotation_date' => '2026-05-22',
        'validity_date' => '2026-06-05',
        'item_type' => ['service', 'product'],
        'item_source_id' => [$fixtures['service']->id, $fixtures['product']->id],
        'quantity' => [2, 1],
        'rate' => [500, 120],
    ]);

    $response->assertRedirect(route('transactions.quotations.index', absolute: false));
    $this->assertDatabaseHas('quotations', [
        'quotation_no' => 'QUO-2026-0001',
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'subtotal' => 1120,
        'tax_amount' => 112,
        'total' => 1232,
        'status' => Quotation::STATUS_DRAFT,
    ]);
    $this->assertDatabaseCount('quotation_items', 2);

    $listResponse = $this->actingAs($user)->get('/transactions/quotations');
    $listResponse->assertSee('QUO-2026-0001');
    $listResponse->assertSee('Acme Stores');
    $listResponse->assertSee('View');
    $listResponse->assertSee('Status');
    $listResponse->assertSee('Edit');
    $listResponse->assertSee('Duplicate');
    $listResponse->assertSee('Delete');
    $listResponse->assertSee('PDF');
    $listResponse->assertSee('Send');
    $listResponse->assertSee('Approve');
    $listResponse->assertSee('Reject');
    $listResponse->assertSee(route('transactions.quotations.show', Quotation::query()->first(), absolute: false), false);
});

test('authenticated users can view and print a quotation with company logo', function () {
    $user = User::factory()->create();
    $fixtures = quotationFixtures();
    CompanySetting::create([
        'singleton_key' => CompanySetting::SINGLETON_KEY,
        'company_name' => 'Hariman Nexus',
        'company_email' => 'hello@hariman.test',
        'company_phone_code' => '+234',
        'company_phone_local' => '8012345678',
        'company_location_country' => 'Nigeria',
        'company_location' => 'Lagos',
        'company_logo_web_path' => 'uploads/settings/company-logo.png',
    ]);

    $quotation = Quotation::create([
        'quotation_no' => 'QUO-2026-0009',
        'sequence_no' => 9,
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
        'status' => Quotation::STATUS_DRAFT,
    ]);
    $quotation->items()->create([
        'item_type' => 'service',
        'item_source_id' => $fixtures['service']->id,
        'item_name' => 'Website Design',
        'quantity' => 2,
        'rate' => 500,
        'line_total' => 1000,
    ]);

    $response = $this->actingAs($user)->get(route('transactions.quotations.show', $quotation, absolute: false));

    $response->assertOk();
    $response->assertSee('QUO-2026-0009');
    $response->assertSee('Print');
    $response->assertSee('Back');
    $response->assertSee('uploads/settings/company-logo.png', false);
    $response->assertSee('Website Design');
    $response->assertSee('50% advance payment.');
    $response->assertDontSee('LC-2026-0001');
});

test('status page can update item rates and approve quotation', function () {
    $user = User::factory()->create();
    $fixtures = quotationFixtures();

    $quotation = Quotation::create([
        'quotation_no' => 'QUO-2026-0010',
        'sequence_no' => 10,
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'tax_setting_id' => $fixtures['tax']->id,
        'term_condition_id' => $fixtures['term']->id,
        'quotation_date' => '2026-05-22',
        'validity_date' => '2026-06-05',
        'subtotal' => 500,
        'tax_rate_percent' => 10,
        'tax_amount' => 50,
        'total' => 550,
        'status' => Quotation::STATUS_DRAFT,
    ]);
    $item = $quotation->items()->create([
        'item_type' => 'service',
        'item_source_id' => $fixtures['service']->id,
        'item_name' => 'Website Design',
        'quantity' => 2,
        'rate' => 250,
        'line_total' => 500,
    ]);

    $response = $this->actingAs($user)->get(route('transactions.quotations.status', $quotation, absolute: false));
    $response->assertOk();
    $response->assertSee('Quotation Status');
    $response->assertSee('Approve');
    $response->assertSee('Reject');

    $update = $this->actingAs($user)->patch(route('transactions.quotations.status.update', $quotation, absolute: false), [
        'rate' => [$item->id => 600],
        'action' => 'approve',
    ]);

    $update->assertRedirect(route('transactions.quotations.status', $quotation, absolute: false));
    $quotation->refresh();
    $item->refresh();

    expect($item->rate)->toBe('600.00')
        ->and($item->line_total)->toBe('1200.00')
        ->and($quotation->subtotal)->toBe('1200.00')
        ->and($quotation->tax_amount)->toBe('120.00')
        ->and($quotation->total)->toBe('1320.00')
        ->and($quotation->status)->toBe(Quotation::STATUS_APPROVED);
});

test('authenticated users can download quotation pdf', function () {
    $user = User::factory()->create();
    $fixtures = quotationFixtures();

    $quotation = Quotation::create([
        'quotation_no' => 'QUO-2026-0011',
        'sequence_no' => 11,
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'quotation_date' => '2026-05-22',
        'subtotal' => 500,
        'tax_rate_percent' => 0,
        'tax_amount' => 0,
        'total' => 500,
        'status' => Quotation::STATUS_DRAFT,
    ]);
    $quotation->items()->create([
        'item_type' => 'service',
        'item_source_id' => $fixtures['service']->id,
        'item_name' => 'Website Design',
        'quantity' => 1,
        'rate' => 500,
        'line_total' => 500,
    ]);

    $response = $this->actingAs($user)->get(route('transactions.quotations.pdf', $quotation, absolute: false));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

test('approved quotation status page locks actions and rates', function () {
    $user = User::factory()->create();
    $fixtures = quotationFixtures();

    $quotation = Quotation::create([
        'quotation_no' => 'QUO-2026-0012',
        'sequence_no' => 12,
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'quotation_date' => '2026-05-22',
        'subtotal' => 500,
        'tax_rate_percent' => 0,
        'tax_amount' => 0,
        'total' => 500,
        'status' => Quotation::STATUS_APPROVED,
    ]);
    $item = $quotation->items()->create([
        'item_type' => 'service',
        'item_source_id' => $fixtures['service']->id,
        'item_name' => 'Website Design',
        'quantity' => 1,
        'rate' => 500,
        'line_total' => 500,
    ]);

    $response = $this->actingAs($user)->get(route('transactions.quotations.status', $quotation, absolute: false));
    $response->assertOk();
    $response->assertSee('Rates and approval actions are now locked');
    $response->assertDontSee('name="action" value="approve"', false);
    $response->assertDontSee('name="rate['.$item->id.']"', false);

    $update = $this->actingAs($user)->patch(route('transactions.quotations.status.update', $quotation, absolute: false), [
        'rate' => [$item->id => 900],
        'action' => 'reject',
    ]);

    $update->assertRedirect(route('transactions.quotations.status', $quotation, absolute: false));
    expect($item->refresh()->rate)->toBe('500.00')
        ->and($quotation->refresh()->status)->toBe(Quotation::STATUS_APPROVED);
});

test('approved quotations cannot be edited', function () {
    $user = User::factory()->create();
    $fixtures = quotationFixtures();

    $quotation = Quotation::create([
        'quotation_no' => 'QUO-2026-0013',
        'sequence_no' => 13,
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'quotation_date' => '2026-05-22',
        'subtotal' => 500,
        'tax_rate_percent' => 0,
        'tax_amount' => 0,
        'total' => 500,
        'status' => Quotation::STATUS_APPROVED,
    ]);

    $this->actingAs($user)
        ->get(route('transactions.quotations.edit', $quotation, absolute: false))
        ->assertForbidden();

    $listResponse = $this->actingAs($user)->get(route('transactions.quotations.index', absolute: false));
    $listResponse->assertSee('QUO-2026-0013');
    $listResponse->assertDontSee(route('transactions.quotations.edit', $quotation, absolute: false), false);
});

test('converted quotations with connected invoices cannot be deleted', function () {
    $user = User::factory()->create();
    $fixtures = quotationFixtures();

    $quotation = Quotation::create([
        'quotation_no' => 'QUO-2026-0014',
        'sequence_no' => 14,
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
    Invoice::create([
        'invoice_no' => 'INV-2026-0014',
        'sequence_no' => 14,
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
        ->delete(route('transactions.quotations.destroy', $quotation, absolute: false))
        ->assertRedirect(route('transactions.quotations.index', absolute: false))
        ->assertSessionHas('status', 'quotation-delete-blocked-converted');

    $this->assertDatabaseHas('quotations', ['id' => $quotation->id]);

    $listResponse = $this->actingAs($user)->get(route('transactions.quotations.index', absolute: false));
    $listResponse->assertSee('Converted to invoice');
});
