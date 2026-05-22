<?php

use App\Models\NumberingSetting;
use App\Models\User;

function validNumberingPayload(array $overrides = []): array
{
    return array_merge([
        'separator' => '-',
        'padding' => 4,
        'include_year_for_clients' => '1',
        'include_year_for_invoices' => '1',
        'include_year_for_quotations' => '1',
        'local_client_prefix' => 'LC',
        'abroad_client_prefix' => 'AC',
        'product_prefix' => 'PROD',
        'invoice_prefix' => 'INV',
        'quotation_prefix' => 'QUO',
        'next_local_client_number' => 1,
        'next_abroad_client_number' => 1,
        'next_product_number' => 1,
        'next_invoice_number' => 1,
        'next_quotation_number' => 1,
    ], $overrides);
}

test('numbering page requires authentication', function () {
    $response = $this->get('/settings/numbering');

    $response->assertRedirect(route('login', absolute: false));
});

test('authenticated users can view numbering page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/numbering');

    $response->assertOk();
    $response->assertSee('Numbering');
    $response->assertSee('Number Formats');
    $response->assertSee('Current Preview');
    $response->assertSee('INV-'.now()->year.'-0001');
});

test('numbering required fields are validated', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/settings/numbering', []);

    $response->assertSessionHasErrors([
        'separator',
        'padding',
        'local_client_prefix',
        'invoice_prefix',
        'next_invoice_number',
    ]);
});

test('authenticated users can save numbering settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/settings/numbering', validNumberingPayload([
        'separator' => '/',
        'padding' => 5,
        'invoice_prefix' => 'HNINV',
        'next_invoice_number' => 42,
    ]));

    $response->assertRedirect(route('settings.numbering.edit', absolute: false));
    $this->assertDatabaseHas('numbering_settings', [
        'singleton_key' => NumberingSetting::SINGLETON_KEY,
        'separator' => '/',
        'padding' => 5,
        'invoice_prefix' => 'HNINV',
        'next_invoice_number' => 42,
        'include_year_for_invoices' => true,
    ]);
});

test('year toggles can be disabled', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/settings/numbering', validNumberingPayload([
        'include_year_for_clients' => null,
        'include_year_for_invoices' => null,
        'include_year_for_quotations' => null,
    ]));

    $response->assertRedirect(route('settings.numbering.edit', absolute: false));
    $this->assertDatabaseHas('numbering_settings', [
        'include_year_for_clients' => false,
        'include_year_for_invoices' => false,
        'include_year_for_quotations' => false,
    ]);
});
