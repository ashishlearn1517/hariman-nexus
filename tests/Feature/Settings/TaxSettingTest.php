<?php

use App\Models\TaxSetting;
use App\Models\User;

test('tax settings page requires authentication', function () {
    $response = $this->get('/settings/taxes');

    $response->assertRedirect(route('login', absolute: false));
});

test('authenticated users can view tax settings page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/taxes');

    $response->assertOk();
    $response->assertSee('Tax Settings');
    $response->assertSee('Add Tax');
    $response->assertSee('Tax List');
});

test('tax name and rate are required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/settings/taxes', [
        'status' => TaxSetting::STATUS_ACTIVE,
    ]);

    $response->assertSessionHasErrors(['name', 'rate_percent']);
});

test('authenticated users can create tax settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/settings/taxes', [
        'name' => 'VAT',
        'rate_percent' => '7.5000',
        'description' => 'Default value added tax',
        'is_default' => '1',
        'status' => TaxSetting::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('settings.taxes.index', absolute: false));
    $this->assertDatabaseHas('tax_settings', [
        'name' => 'VAT',
        'rate_percent' => 7.5000,
        'description' => 'Default value added tax',
        'is_default' => true,
        'status' => TaxSetting::STATUS_ACTIVE,
    ]);
});

test('only one tax can be default', function () {
    $user = User::factory()->create();
    $vat = TaxSetting::create([
        'name' => 'VAT',
        'rate_percent' => 7.5,
        'is_default' => true,
        'status' => TaxSetting::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->post('/settings/taxes', [
        'name' => 'GST',
        'rate_percent' => '5.0000',
        'is_default' => '1',
        'status' => TaxSetting::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('settings.taxes.index', absolute: false));
    expect($vat->refresh()->is_default)->toBeFalse();
    $this->assertDatabaseHas('tax_settings', [
        'name' => 'GST',
        'rate_percent' => 5.0000,
        'is_default' => true,
        'status' => TaxSetting::STATUS_ACTIVE,
    ]);
});

test('authenticated users can view and update tax settings', function () {
    $user = User::factory()->create();
    $tax = TaxSetting::create([
        'name' => 'VAT',
        'rate_percent' => 7.5,
        'description' => 'Default value added tax',
        'status' => TaxSetting::STATUS_ACTIVE,
    ]);

    $editResponse = $this->actingAs($user)->get(route('settings.taxes.edit', $tax));

    $editResponse->assertOk();
    $editResponse->assertSee('Edit Tax');

    $response = $this->actingAs($user)->patch(route('settings.taxes.update', $tax), [
        'name' => 'VAT Domestic',
        'rate_percent' => '8.2500',
        'description' => 'Domestic VAT rate',
        'status' => TaxSetting::STATUS_INACTIVE,
    ]);

    $response->assertRedirect(route('settings.taxes.index', absolute: false));
    $this->assertDatabaseHas('tax_settings', [
        'id' => $tax->id,
        'name' => 'VAT Domestic',
        'rate_percent' => 8.2500,
        'description' => 'Domestic VAT rate',
        'status' => TaxSetting::STATUS_INACTIVE,
    ]);
});
