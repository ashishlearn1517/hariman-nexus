<?php

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Hariman Nexus');
    $response->assertSee('Login to Workspace');
    $response->assertSee('Sales Records');
    $response->assertSee('Hariman Solutions');
});
