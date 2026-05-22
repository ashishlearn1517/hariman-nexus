<?php

use App\Models\Project;
use App\Models\User;

test('projects page requires authentication', function () {
    $response = $this->get('/sales/projects');

    $response->assertRedirect(route('login', absolute: false));
});

test('authenticated users can view projects page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/sales/projects');

    $response->assertOk();
    $response->assertSee('Add Project');
    $response->assertSee('Project List');
});

test('project name and start date are required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/sales/projects', [
        'status' => Project::STATUS_ACTIVE,
    ]);

    $response->assertSessionHasErrors(['name', 'start_date']);
});

test('authenticated users can create projects', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/sales/projects', [
        'name' => 'ERP Implementation',
        'start_date' => '2026-06-01',
        'expected_delivery_time' => '8 weeks',
        'awarded_to' => 'Ashish',
        'status' => Project::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('sales.projects.index', absolute: false));
    $this->assertDatabaseHas('projects', [
        'name' => 'ERP Implementation',
        'expected_delivery_time' => '8 weeks',
        'awarded_to' => 'Ashish',
        'status' => Project::STATUS_ACTIVE,
    ]);
});

test('authenticated users can view edit project page', function () {
    $user = User::factory()->create();
    $project = Project::create([
        'name' => 'Website Redesign',
        'start_date' => '2026-06-05',
        'status' => Project::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->get(route('sales.projects.edit', $project));

    $response->assertOk();
    $response->assertSee('Edit Project');
    $response->assertSee('Website Redesign');
});

test('authenticated users can update project details', function () {
    $user = User::factory()->create();
    $project = Project::create([
        'name' => 'Website Redesign',
        'start_date' => '2026-06-05',
        'status' => Project::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->patch(route('sales.projects.update', $project), [
        'name' => 'Website Redesign Phase 2',
        'start_date' => '2026-07-01',
        'expected_delivery_time' => '10 weeks',
        'awarded_to' => 'Nexus Delivery Team',
        'status' => Project::STATUS_INACTIVE,
    ]);

    $response->assertRedirect(route('sales.projects.index', absolute: false));
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Website Redesign Phase 2',
        'expected_delivery_time' => '10 weeks',
        'awarded_to' => 'Nexus Delivery Team',
        'status' => Project::STATUS_INACTIVE,
    ]);
});
