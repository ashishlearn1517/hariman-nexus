<?php

use App\Models\Product;
use App\Models\User;

test('products page requires authentication', function () {
    $response = $this->get('/sales/products');

    $response->assertRedirect(route('login', absolute: false));
});

test('authenticated users can view products page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/sales/products');

    $response->assertOk();
    $response->assertSee('Add Product');
    $response->assertSee('Product List');
    $response->assertSee('PROD-0001');
    $response->assertSee('Search products...');
});

test('authenticated users can search products', function () {
    $user = User::factory()->create();
    Product::create([
        'product_code' => 'PROD-0001',
        'name' => 'HP 85A Cartridge',
        'description' => 'Original toner cartridge',
        'unit_price' => 125.50,
        'status' => Product::STATUS_ACTIVE,
    ]);
    Product::create([
        'product_code' => 'PROD-0002',
        'name' => 'Dell Monitor',
        'description' => 'Display screen',
        'unit_price' => 220,
        'status' => Product::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->get('/sales/products?search=Cartridge');

    $response->assertOk();
    $response->assertSee('HP 85A Cartridge');
    $response->assertDontSee('Dell Monitor');
});

test('product name and unit price are required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/sales/products', [
        'status' => Product::STATUS_ACTIVE,
    ]);

    $response->assertSessionHasErrors(['name', 'unit_price']);
});

test('authenticated users can create products with generated code', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/sales/products', [
        'name' => 'HP 85A Cartridge',
        'description' => 'Original toner cartridge',
        'unit_price' => '125.50',
        'status' => Product::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('sales.products.index', absolute: false));
    $this->assertDatabaseHas('products', [
        'product_code' => 'PROD-0001',
        'name' => 'HP 85A Cartridge',
        'description' => 'Original toner cartridge',
        'unit_price' => 125.50,
        'status' => Product::STATUS_ACTIVE,
    ]);
});

test('authenticated users can view edit product page', function () {
    $user = User::factory()->create();
    $product = Product::create([
        'product_code' => 'PROD-0001',
        'name' => 'HP 85A Cartridge',
        'description' => 'Original toner cartridge',
        'unit_price' => 125.50,
        'status' => Product::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->get(route('sales.products.edit', $product));

    $response->assertOk();
    $response->assertSee('Edit Product');
    $response->assertSee('HP 85A Cartridge');
});

test('authenticated users can update products', function () {
    $user = User::factory()->create();
    $product = Product::create([
        'product_code' => 'PROD-0001',
        'name' => 'HP 85A Cartridge',
        'description' => 'Original toner cartridge',
        'unit_price' => 125.50,
        'status' => Product::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->patch(route('sales.products.update', $product), [
        'name' => 'HP 85A Cartridge Pack',
        'description' => 'Pack of two original toner cartridges',
        'unit_price' => 240,
        'status' => Product::STATUS_INACTIVE,
    ]);

    $response->assertRedirect(route('sales.products.index', absolute: false));
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'product_code' => 'PROD-0001',
        'name' => 'HP 85A Cartridge Pack',
        'description' => 'Pack of two original toner cartridges',
        'unit_price' => 240,
        'status' => Product::STATUS_INACTIVE,
    ]);
});
