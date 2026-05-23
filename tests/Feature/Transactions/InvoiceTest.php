<?php

use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Currency;
use App\Models\EmailSetting;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\NumberingSetting;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\TaxSetting;
use App\Models\TermCondition;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

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
    $response->assertSee('Search item...');
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
    $listResponse->assertSee('Reminder');
    $listResponse->assertSee('Overdue');
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
    CompanySetting::create([
        'singleton_key' => CompanySetting::SINGLETON_KEY,
        'company_name' => 'Hariman Solutions',
        'company_email' => 'accounts@hariman.com',
        'company_phone_code' => '+91',
        'company_phone_local' => '9876543210',
        'company_location' => 'Jodhpur',
        'company_location_country' => 'IN',
        'company_address' => "Sardarpura\nJodhpur, Rajasthan 342003",
    ]);

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
        ->assertSee('Add Payment Entry')
        ->assertSee('Payment Entries &amp; Receipts', false);

    $this->actingAs($user)
        ->get(route('transactions.invoices.show', $invoice, absolute: false))
        ->assertOk()
        ->assertSee('Email:')
        ->assertSee('accounts@hariman.com')
        ->assertSee('Contact:')
        ->assertSee('+91 9876543210')
        ->assertSee('Address:')
        ->assertSee('Sardarpura')
        ->assertSee('Jodhpur, Rajasthan 342003')
        ->assertDontSee('LC-2026-0001');

    $this->actingAs($user)
        ->get(route('transactions.invoices.pdf', $invoice, absolute: false))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('payment entries support partial and full payment status updates with receipts', function () {
    $user = User::factory()->create();
    $fixtures = invoiceFixtures();
    $invoice = Invoice::create([
        'invoice_no' => 'INV-2026-0011',
        'sequence_no' => 11,
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'invoice_date' => '2026-05-22',
        'due_date' => '2026-06-05',
        'subtotal' => 1000,
        'tax_rate_percent' => 0,
        'tax_amount' => 0,
        'total' => 1000,
        'balance_due' => 1000,
        'status' => Invoice::STATUS_DRAFT,
    ]);

    $partial = $this->actingAs($user)->post(route('transactions.invoices.payments.store', $invoice, absolute: false), [
        'payment_date' => '2026-05-23',
        'amount' => 400,
        'payment_method' => 'bank_transfer',
        'receipt_number' => 'RCPT-001',
        'reference' => 'UTR123',
        'receipt_file' => UploadedFile::fake()->create('receipt.pdf', 16, 'application/pdf'),
    ]);

    $partial->assertRedirect(route('transactions.invoices.payment-status', $invoice, absolute: false));
    $invoice->refresh();
    expect($invoice->amount_paid)->toBe('400.00')
        ->and($invoice->balance_due)->toBe('600.00')
        ->and($invoice->status)->toBe(Invoice::STATUS_PARTIALLY_PAID);
    $payment = InvoicePayment::first();
    expect($payment->receipt_web_path)->not->toBeNull();
    File::deleteDirectory(public_path('uploads/receipts'));

    $this->actingAs($user)->post(route('transactions.invoices.payments.store', $invoice, absolute: false), [
        'payment_date' => '2026-05-24',
        'amount' => 600,
        'payment_method' => 'cash',
    ])->assertRedirect(route('transactions.invoices.payment-status', $invoice, absolute: false));

    $invoice->refresh();
    expect($invoice->amount_paid)->toBe('1000.00')
        ->and($invoice->balance_due)->toBe('0.00')
        ->and($invoice->status)->toBe(Invoice::STATUS_PAID)
        ->and($invoice->paid_at)->not->toBeNull();
});

test('payment status page marks unpaid overdue invoices and deleting payments recalculates balance', function () {
    $user = User::factory()->create();
    $fixtures = invoiceFixtures();
    $invoice = Invoice::create([
        'invoice_no' => 'INV-2026-0012',
        'sequence_no' => 12,
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'invoice_date' => '2026-04-01',
        'due_date' => '2026-04-15',
        'subtotal' => 1000,
        'tax_rate_percent' => 0,
        'tax_amount' => 0,
        'total' => 1000,
        'amount_paid' => 0,
        'balance_due' => 1000,
        'status' => Invoice::STATUS_DRAFT,
    ]);

    $this->actingAs($user)->get(route('transactions.invoices.payment-status', $invoice, absolute: false))->assertOk();
    expect($invoice->refresh()->status)->toBe(Invoice::STATUS_OVERDUE);

    $payment = $invoice->payments()->create([
        'payment_date' => '2026-05-22',
        'amount' => 250,
        'payment_method' => 'cash',
    ]);

    $this->actingAs($user)->delete(route('transactions.invoices.payments.destroy', [$invoice, $payment], false))
        ->assertRedirect(route('transactions.invoices.payment-status', $invoice, absolute: false));

    $invoice->refresh();
    expect($invoice->amount_paid)->toBe('0.00')
        ->and($invoice->balance_due)->toBe('1000.00')
        ->and($invoice->status)->toBe(Invoice::STATUS_OVERDUE);
});

test('reminder email can be sent only one day before invoice due date', function () {
    Mail::fake();
    $this->travelTo('2026-05-22 10:00:00');

    $user = User::factory()->create();
    $fixtures = invoiceFixtures();
    EmailSetting::create(array_merge(EmailSetting::defaults(), [
        'mail_host' => 'smtp.example.com',
        'mail_from_address' => 'billing@example.com',
        'mail_password' => 'secret',
    ]));

    $invoice = Invoice::create([
        'invoice_no' => 'INV-2026-0013',
        'sequence_no' => 13,
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'invoice_date' => '2026-05-22',
        'due_date' => '2026-05-23',
        'subtotal' => 1000,
        'tax_rate_percent' => 0,
        'tax_amount' => 0,
        'total' => 1000,
        'balance_due' => 1000,
        'status' => Invoice::STATUS_SENT,
    ]);

    $this->actingAs($user)
        ->post(route('transactions.invoices.send-reminder', $invoice, absolute: false))
        ->assertRedirect(route('transactions.invoices.index', absolute: false))
        ->assertSessionHas('status', 'invoice-reminder-sent');

    $invoice->update(['due_date' => '2026-05-24']);

    $this->actingAs($user)
        ->post(route('transactions.invoices.send-reminder', $invoice, absolute: false))
        ->assertRedirect(route('transactions.invoices.index', absolute: false))
        ->assertSessionHas('status', 'invoice-reminder-not-ready');
});

test('overdue email can be sent only after the due date is over', function () {
    Mail::fake();
    $this->travelTo('2026-05-22 10:00:00');

    $user = User::factory()->create();
    $fixtures = invoiceFixtures();
    EmailSetting::create(array_merge(EmailSetting::defaults(), [
        'mail_host' => 'smtp.example.com',
        'mail_from_address' => 'billing@example.com',
        'mail_password' => 'secret',
    ]));

    $invoice = Invoice::create([
        'invoice_no' => 'INV-2026-0014',
        'sequence_no' => 14,
        'client_id' => $fixtures['client']->id,
        'project_id' => $fixtures['project']->id,
        'currency_id' => $fixtures['currency']->id,
        'invoice_date' => '2026-05-20',
        'due_date' => '2026-05-21',
        'subtotal' => 1000,
        'tax_rate_percent' => 0,
        'tax_amount' => 0,
        'total' => 1000,
        'balance_due' => 1000,
        'status' => Invoice::STATUS_SENT,
    ]);

    $this->actingAs($user)
        ->post(route('transactions.invoices.send-overdue', $invoice, absolute: false))
        ->assertRedirect(route('transactions.invoices.index', absolute: false))
        ->assertSessionHas('status', 'invoice-overdue-sent');

    $invoice->update(['due_date' => '2026-05-22']);

    $this->actingAs($user)
        ->post(route('transactions.invoices.send-overdue', $invoice, absolute: false))
        ->assertRedirect(route('transactions.invoices.index', absolute: false))
        ->assertSessionHas('status', 'invoice-overdue-not-ready');
});
