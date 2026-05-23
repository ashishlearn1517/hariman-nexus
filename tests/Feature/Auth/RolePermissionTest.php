<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

function userForRole(string $role): User
{
    test()->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create(['role' => $role]);
    $user->syncRoles($role);

    return $user;
}

test('viewer users have read only access', function () {
    $viewer = userForRole(User::ROLE_VIEWER);

    $this->actingAs($viewer)->get('/transactions/invoices')->assertOk();
    $this->actingAs($viewer)->post('/transactions/invoices', [])->assertForbidden();
    $this->actingAs($viewer)->get('/settings/company')->assertForbidden();
    $this->actingAs($viewer)->get('/register')->assertForbidden();
});

test('admin users can register and activate users but cannot edit or delete them', function () {
    $admin = userForRole(User::ROLE_ADMIN);
    $target = userForRole(User::ROLE_VIEWER);

    $this->actingAs($admin)->get('/transactions/invoices')->assertOk();
    $this->actingAs($admin)->get('/sales/projects')->assertOk();
    $this->actingAs($admin)->get('/settings/company')->assertForbidden();
    $this->actingAs($admin)->get('/register')->assertOk();
    $this->actingAs($admin)->get(route('users.edit', $target, false))->assertForbidden();
    $this->actingAs($admin)->delete(route('users.destroy', $target, false))->assertForbidden();
    $this->actingAs($admin)->patch(route('users.status', $target, false))->assertRedirect();

    expect($target->refresh()->is_active)->toBeFalse();
});

test('accountant users can manage invoices and payments but not operations setup', function () {
    $accountant = userForRole(User::ROLE_ACCOUNTANT);

    $this->actingAs($accountant)->get('/transactions/invoices')->assertOk();
    $this->actingAs($accountant)->post('/transactions/invoices', [])->assertSessionHasErrors();
    $this->actingAs($accountant)->get('/sales/projects')->assertForbidden();
    $this->actingAs($accountant)->get('/settings/company')->assertForbidden();
});

test('inactive users cannot log in', function () {
    $user = userForRole(User::ROLE_VIEWER);
    $user->forceFill([
        'email' => 'inactive@example.com',
        'is_active' => false,
    ])->save();

    $this->post('/login', [
        'email' => 'inactive@example.com',
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});
