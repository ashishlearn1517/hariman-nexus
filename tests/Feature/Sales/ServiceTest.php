<?php

use App\Models\Service;
use App\Models\User;

test('services page requires authentication', function () {
    $response = $this->get('/sales/services');

    $response->assertRedirect(route('login', absolute: false));
});

test('authenticated users can view services page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/sales/services');

    $response->assertOk();
    $response->assertSee('Add Service');
    $response->assertSee('Service List');
});

test('service core fields are required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/sales/services', [
        'status' => Service::STATUS_ACTIVE,
    ]);

    $response->assertSessionHasErrors(['short_name', 'long_name', 'default_rate']);
});

test('authenticated users can create services', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/sales/services', [
        'short_name' => 'SEO',
        'long_name' => 'Search Engine Optimization Retainer',
        'default_rate' => '1500.00',
        'status' => Service::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('sales.services.index', absolute: false));
    $this->assertDatabaseHas('services', [
        'short_name' => 'SEO',
        'long_name' => 'Search Engine Optimization Retainer',
        'default_rate' => 1500,
        'status' => Service::STATUS_ACTIVE,
    ]);
});

test('authenticated users can view edit service page', function () {
    $user = User::factory()->create();
    $service = Service::create([
        'short_name' => 'AMC',
        'long_name' => 'Annual Maintenance Contract',
        'default_rate' => 2500,
        'status' => Service::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->get(route('sales.services.edit', $service));

    $response->assertOk();
    $response->assertSee('Edit Service');
    $response->assertSee('Annual Maintenance Contract');
});

test('authenticated users can update services', function () {
    $user = User::factory()->create();
    $service = Service::create([
        'short_name' => 'AMC',
        'long_name' => 'Annual Maintenance Contract',
        'default_rate' => 2500,
        'status' => Service::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->patch(route('sales.services.update', $service), [
        'short_name' => 'AMC+',
        'long_name' => 'Premium Annual Maintenance Contract',
        'default_rate' => 3200,
        'status' => Service::STATUS_INACTIVE,
    ]);

    $response->assertRedirect(route('sales.services.index', absolute: false));
    $this->assertDatabaseHas('services', [
        'id' => $service->id,
        'short_name' => 'AMC+',
        'long_name' => 'Premium Annual Maintenance Contract',
        'default_rate' => 3200,
        'status' => Service::STATUS_INACTIVE,
    ]);
});
