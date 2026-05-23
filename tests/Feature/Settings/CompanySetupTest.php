<?php

use App\Models\CompanySetting;
use App\Models\User;

test('company setup page requires authentication', function () {
    $response = $this->get('/settings/company');

    $response->assertRedirect(route('login', absolute: false));
});

test('authenticated users can view company setup page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/company');

    $response->assertOk();
    $response->assertSee('Company Setup');
    $response->assertSee('Company Profile');
    $response->assertSee('Company Address');
    $response->assertSee('Afghanistan (+93)');
    $response->assertSee('Zimbabwe (ZW)');
});

test('company setup country options include the full country list', function () {
    expect(CompanySetting::countryOptions())
        ->toHaveCount(250)
        ->toHaveKey('IN')
        ->toHaveKey('US')
        ->toHaveKey('ZW');
});

test('company name is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/settings/company', [
        'company_email' => 'accounts@hariman.com',
    ]);

    $response->assertSessionHasErrors(['company_name']);
});

test('authenticated users can save company setup', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/settings/company', [
        'company_name' => 'Hariman Nexus',
        'company_email' => 'accounts@hariman.com',
        'company_phone_country' => 'IN',
        'company_phone_local' => '9876543210',
        'company_location_country' => 'IN',
        'company_address' => "Sardarpura\nJodhpur, Rajasthan 342003",
        'website' => 'https://hariman.example',
        'tax_registration_number' => 'GST-12345',
        'payment_label' => 'Bank Transfer',
        'payment_reference' => 'Use invoice number',
        'bank_details' => "Hariman Bank\nAccount name: Hariman Solutions",
    ]);

    $response->assertRedirect(route('settings.company.edit', absolute: false));
    $this->assertDatabaseHas('company_settings', [
        'singleton_key' => CompanySetting::SINGLETON_KEY,
        'company_name' => 'Hariman Nexus',
        'company_email' => 'accounts@hariman.com',
        'company_phone_country' => 'IN',
        'company_phone_code' => '+91',
        'company_phone_local' => '9876543210',
        'company_location' => 'India',
        'company_address' => "Sardarpura\nJodhpur, Rajasthan 342003",
        'website' => 'https://hariman.example',
        'tax_registration_number' => 'GST-12345',
        'payment_label' => 'Bank Transfer',
    ]);
});
