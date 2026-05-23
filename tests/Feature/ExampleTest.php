<?php

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Hariman Nexus');
    $response->assertSee('Invoicing, Quotations, Payments and Business Operations Platform');
    $response->assertSee('SoftwareApplication');
    $response->assertSee('Login to Workspace');
    $response->assertSee('Quotations to invoices');
    $response->assertSee('Hariman Technologies');
    $response->assertSee('Ashish');
    $response->assertSee('info@hariman.co.in');
    $response->assertSee('+91 8233990399');
});

it('renders the hariman technologies page', function () {
    $response = $this->get('/hariman');

    $response->assertStatus(200);
    $response->assertSee('Hariman Technologies');
    $response->assertSee('ERP, Web, Mobile, AI and Digital Business Solutions');
    $response->assertSee('Organization');
    $response->assertSee('Hariman Forge');
    $response->assertSee('ERP Solutions');
    $response->assertSee('AI, ML, and LLM Solutions');
    $response->assertSee('Spare part management application coming soon.');
    $response->assertSee('info@hariman.co.in');
    $response->assertSee('+91 8233990399');
});
