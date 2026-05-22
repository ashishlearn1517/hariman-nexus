<?php

use App\Models\Currency;
use App\Models\User;

test('currency settings page requires authentication', function () {
    $response = $this->get('/settings/currencies');

    $response->assertRedirect(route('login', absolute: false));
});

test('authenticated users can view currency settings page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/currencies');

    $response->assertOk();
    $response->assertSee('Currency Settings');
    $response->assertSee('Add Currency');
    $response->assertSee('Currency List');
    $response->assertSee('Search currency code or name');
    $response->assertSee('currency-selected');
});

test('currency code and exchange rate are required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/settings/currencies', [
        'status' => Currency::STATUS_ACTIVE,
    ]);

    $response->assertSessionHasErrors(['code', 'exchange_rate']);
});

test('authenticated users can create currencies', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/settings/currencies', [
        'code' => 'usd',
        'exchange_rate' => '1.000000',
        'is_default' => '1',
        'status' => Currency::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('settings.currencies.index', absolute: false));
    $this->assertDatabaseHas('currencies', [
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => '$',
        'exchange_rate' => 1,
        'is_default' => true,
        'status' => Currency::STATUS_ACTIVE,
    ]);
});

test('only one currency can be default', function () {
    $user = User::factory()->create();
    $usd = Currency::create([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => '$',
        'exchange_rate' => 1,
        'is_default' => true,
        'status' => Currency::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->post('/settings/currencies', [
        'code' => 'GBP',
        'exchange_rate' => '0.790000',
        'is_default' => '1',
        'status' => Currency::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('settings.currencies.index', absolute: false));
    expect($usd->refresh()->is_default)->toBeFalse();
    $this->assertDatabaseHas('currencies', [
        'code' => 'GBP',
        'is_default' => true,
        'exchange_rate' => 1,
        'status' => Currency::STATUS_ACTIVE,
    ]);
});

test('authenticated users can view and update currencies', function () {
    $user = User::factory()->create();
    $currency = Currency::create([
        'code' => 'EUR',
        'name' => 'Euro',
        'symbol' => 'EUR',
        'exchange_rate' => 0.920000,
        'status' => Currency::STATUS_ACTIVE,
    ]);

    $editResponse = $this->actingAs($user)->get(route('settings.currencies.edit', $currency));

    $editResponse->assertOk();
    $editResponse->assertSee('Edit Currency');

    $response = $this->actingAs($user)->patch(route('settings.currencies.update', $currency), [
        'code' => 'EUR',
        'exchange_rate' => '0.930000',
        'status' => Currency::STATUS_INACTIVE,
    ]);

    $response->assertRedirect(route('settings.currencies.index', absolute: false));
    $this->assertDatabaseHas('currencies', [
        'id' => $currency->id,
        'code' => 'EUR',
        'name' => 'Euro',
        'symbol' => '€',
        'exchange_rate' => 0.930000,
        'status' => Currency::STATUS_INACTIVE,
    ]);
});
