<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/register');

    $response->assertStatus(200);
});

test('registration screen requires authentication', function () {
    $response = $this->get('/register');

    $response->assertRedirect(route('login', absolute: false));
});

test('authenticated users can create users with roles', function () {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin)->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => User::ROLE_ACCOUNTANT,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticatedAs($admin);
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'role' => User::ROLE_ACCOUNTANT,
    ]);
    $response->assertRedirect(route('register', absolute: false));
});
