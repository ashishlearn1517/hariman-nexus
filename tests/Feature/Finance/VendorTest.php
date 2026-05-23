<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Vendor;
use App\Models\User;

test('vendors page requires authentication', function () {
    $this->get('/finance/vendors')->assertRedirect(route('login', absolute: false));
});

test('authenticated users can create search update and archive vendors', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/finance/vendors', [
            'vendor_name' => 'Airtel Business',
            'contact_person' => 'Ravi',
            'phone' => '+918233990399',
            'email' => 'vendor@example.com',
            'address' => 'Jodhpur',
            'tax_number' => 'GST123',
            'payment_terms' => 'Net 15',
            'status' => Vendor::STATUS_ACTIVE,
        ])
        ->assertRedirect(route('finance.vendors.index', absolute: false));

    $vendor = Vendor::first();

    expect($vendor)
        ->vendor_code->toBe('VEN001')
        ->vendor_name->toBe('Airtel Business')
        ->created_by->toBe($user->id);

    $this->actingAs($user)
        ->get('/finance/vendors?search=Airtel')
        ->assertOk()
        ->assertSee('VEN001')
        ->assertSee('Airtel Business');

    $this->actingAs($user)
        ->patch("/finance/vendors/{$vendor->id}", [
            'vendor_name' => 'Airtel Enterprise',
            'contact_person' => 'Ravi Sharma',
            'phone' => '+918233990399',
            'email' => 'enterprise@example.com',
            'address' => 'India',
            'tax_number' => 'GST999',
            'payment_terms' => 'Due on receipt',
            'status' => Vendor::STATUS_INACTIVE,
        ])
        ->assertRedirect(route('finance.vendors.index', absolute: false));

    $this->assertDatabaseHas('vendors', [
        'id' => $vendor->id,
        'vendor_name' => 'Airtel Enterprise',
        'status' => Vendor::STATUS_INACTIVE,
    ]);

    $this->actingAs($user)
        ->delete("/finance/vendors/{$vendor->id}")
        ->assertRedirect(route('finance.vendors.index', absolute: false));

    $this->assertSoftDeleted('vendors', ['id' => $vendor->id]);
});

test('vendors linked to expenses cannot be archived', function () {
    $user = User::factory()->create();
    $vendor = Vendor::create([
        'vendor_code' => 'VEN001',
        'vendor_name' => 'Fuel Supplier',
        'status' => Vendor::STATUS_ACTIVE,
    ]);
    $category = ExpenseCategory::create([
        'category_code' => 'EXC001',
        'category_name' => 'Fuel',
        'status' => ExpenseCategory::STATUS_ACTIVE,
    ]);

    Expense::create([
        'expense_no' => 'EXP-2026-0001',
        'sequence_no' => 1,
        'expense_date' => '2026-05-23',
        'expense_category_id' => $category->id,
        'vendor_id' => $vendor->id,
        'vendor_name' => $vendor->vendor_name,
        'amount' => 100,
        'tax_amount' => 0,
        'total_amount' => 100,
        'status' => Expense::STATUS_DRAFT,
    ]);

    $this->actingAs($user)
        ->delete("/finance/vendors/{$vendor->id}")
        ->assertSessionHas('status', 'vendor-delete-blocked');

    expect($vendor->fresh()->deleted_at)->toBeNull();
});
