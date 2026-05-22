<?php

use App\Models\TermCondition;
use App\Models\User;

test('terms page requires authentication', function () {
    $response = $this->get('/sales/terms');

    $response->assertRedirect(route('login', absolute: false));
});

test('authenticated users can view terms page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/sales/terms');

    $response->assertOk();
    $response->assertSee('Add Term');
    $response->assertSee('Terms List');
});

test('term name and content are required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/sales/terms', [
        'status' => TermCondition::STATUS_ACTIVE,
    ]);

    $response->assertSessionHasErrors(['name', 'content']);
});

test('authenticated users can create terms', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/sales/terms', [
        'name' => 'Standard Payment Terms',
        'content' => 'Payment is due within 30 days of invoice date.',
        'status' => TermCondition::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('sales.terms.index', absolute: false));
    $this->assertDatabaseHas('terms_conditions', [
        'name' => 'Standard Payment Terms',
        'content' => 'Payment is due within 30 days of invoice date.',
        'status' => TermCondition::STATUS_ACTIVE,
    ]);
});

test('terms list renders clickable term content trigger', function () {
    $user = User::factory()->create();
    TermCondition::create([
        'name' => 'Standard Payment Terms',
        'content' => 'Payment is due within 30 days of invoice date.',
        'status' => TermCondition::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->get('/sales/terms');

    $response->assertOk();
    $response->assertSee('Standard Payment Terms');
    $response->assertSee('Term Content');
});

test('authenticated users can view edit term page', function () {
    $user = User::factory()->create();
    $term = TermCondition::create([
        'name' => 'Standard Payment Terms',
        'content' => 'Payment is due within 30 days of invoice date.',
        'status' => TermCondition::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->get(route('sales.terms.edit', $term));

    $response->assertOk();
    $response->assertSee('Edit Term');
    $response->assertSee('Standard Payment Terms');
});

test('authenticated users can update terms', function () {
    $user = User::factory()->create();
    $term = TermCondition::create([
        'name' => 'Standard Payment Terms',
        'content' => 'Payment is due within 30 days of invoice date.',
        'status' => TermCondition::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->patch(route('sales.terms.update', $term), [
        'name' => 'Advance Payment Terms',
        'content' => 'Payment is due before delivery.',
        'status' => TermCondition::STATUS_INACTIVE,
    ]);

    $response->assertRedirect(route('sales.terms.index', absolute: false));
    $this->assertDatabaseHas('terms_conditions', [
        'id' => $term->id,
        'name' => 'Advance Payment Terms',
        'content' => 'Payment is due before delivery.',
        'status' => TermCondition::STATUS_INACTIVE,
    ]);
});
