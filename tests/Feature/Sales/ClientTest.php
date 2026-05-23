<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\User;

function clientProject(): Project
{
    return Project::create([
        'name' => 'ERP Implementation',
        'start_date' => '2026-06-01',
        'status' => Project::STATUS_ACTIVE,
    ]);
}

test('clients page requires authentication', function () {
    $response = $this->get('/sales/clients');

    $response->assertRedirect(route('login', absolute: false));
});

test('authenticated users can view clients page', function () {
    $user = User::factory()->create();
    clientProject();

    $response = $this->actingAs($user)->get('/sales/clients');

    $response->assertOk();
    $response->assertSee('Add Client');
    $response->assertSee('Client List');
    $response->assertSee('Search clients...');
});

test('authenticated users can search clients', function () {
    $user = User::factory()->create();
    $project = clientProject();
    Client::create([
        'client_code' => 'LC-2026-0001',
        'sequence_no' => 1,
        'project_id' => $project->id,
        'name' => 'Acme Stores',
        'client_type' => Client::TYPE_LOCAL,
        'email' => 'billing@acme.test',
        'phone' => '+919999999999',
        'address' => 'Mumbai',
        'status' => Client::STATUS_ACTIVE,
    ]);
    Client::create([
        'client_code' => 'LC-2026-0002',
        'sequence_no' => 2,
        'project_id' => $project->id,
        'name' => 'Northline Works',
        'client_type' => Client::TYPE_LOCAL,
        'email' => 'accounts@northline.test',
        'phone' => '+918888888888',
        'address' => 'Delhi',
        'status' => Client::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->get('/sales/clients?search=Acme');

    $response->assertOk();
    $response->assertSee('Acme Stores');
    $response->assertDontSee('Northline Works');
});

test('client core fields are required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/sales/clients', [
        'status' => Client::STATUS_ACTIVE,
    ]);

    $response->assertSessionHasErrors(['project_id', 'name', 'client_type', 'email', 'phone', 'address']);
});

test('authenticated users can create clients with generated code', function () {
    $user = User::factory()->create();
    $project = clientProject();

    $response = $this->actingAs($user)->post('/sales/clients', [
        'project_id' => $project->id,
        'name' => 'Acme Stores',
        'client_type' => Client::TYPE_LOCAL,
        'email' => 'billing@acme.test',
        'phone' => '+919999999999',
        'address' => 'Mumbai',
        'tax_applicable' => '1',
        'tax_percent' => '18',
        'status' => Client::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('sales.clients.index', absolute: false));
    $this->assertDatabaseHas('clients', [
        'client_code' => 'LC-'.now()->year.'-0001',
        'name' => 'Acme Stores',
        'tax_applicable' => true,
        'tax_percent' => 18,
    ]);
});

test('authenticated users can view edit client page', function () {
    $user = User::factory()->create();
    $project = clientProject();
    $client = Client::create([
        'client_code' => 'LC-2026-0001',
        'sequence_no' => 1,
        'project_id' => $project->id,
        'name' => 'Acme Stores',
        'client_type' => Client::TYPE_LOCAL,
        'email' => 'billing@acme.test',
        'phone' => '+919999999999',
        'address' => 'Mumbai',
        'status' => Client::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->get(route('sales.clients.edit', $client));

    $response->assertOk();
    $response->assertSee('Edit Client');
    $response->assertSee('Acme Stores');
});

test('authenticated users can update client details', function () {
    $user = User::factory()->create();
    $project = clientProject();
    $client = Client::create([
        'client_code' => 'LC-2026-0001',
        'sequence_no' => 1,
        'project_id' => $project->id,
        'name' => 'Acme Stores',
        'client_type' => Client::TYPE_LOCAL,
        'email' => 'billing@acme.test',
        'phone' => '+919999999999',
        'address' => 'Mumbai',
        'status' => Client::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->patch(route('sales.clients.update', $client), [
        'project_id' => $project->id,
        'name' => 'Acme Global',
        'client_type' => Client::TYPE_ABROAD,
        'email' => 'accounts@acme.test',
        'phone' => '+441234567890',
        'address' => 'London',
        'status' => Client::STATUS_INACTIVE,
    ]);

    $response->assertRedirect(route('sales.clients.index', absolute: false));
    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'client_code' => 'LC-2026-0001',
        'name' => 'Acme Global',
        'client_type' => Client::TYPE_ABROAD,
        'status' => Client::STATUS_INACTIVE,
    ]);
});
