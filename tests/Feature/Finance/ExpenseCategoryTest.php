<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;

test('expense categories page requires authentication', function () {
    $this->get('/finance/expense-categories')->assertRedirect(route('login', absolute: false));
});

test('authenticated users can create search update and archive expense categories', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/finance/expense-categories', [
        'category_name' => 'Internet',
        'description' => 'Connectivity expenses',
        'status' => ExpenseCategory::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('finance.expense-categories.index', absolute: false));
    $category = ExpenseCategory::first();

    expect($category)
        ->category_code->toBe('EXC001')
        ->category_name->toBe('Internet')
        ->created_by->toBe($user->id);

    $this->actingAs($user)
        ->get('/finance/expense-categories?search=Internet')
        ->assertOk()
        ->assertSee('EXC001')
        ->assertSee('Internet');

    $this->actingAs($user)
        ->patch("/finance/expense-categories/{$category->id}", [
            'category_name' => 'Office Internet',
            'description' => 'Broadband and data',
            'status' => ExpenseCategory::STATUS_INACTIVE,
        ])
        ->assertRedirect(route('finance.expense-categories.index', absolute: false));

    $this->assertDatabaseHas('expense_categories', [
        'id' => $category->id,
        'category_name' => 'Office Internet',
        'status' => ExpenseCategory::STATUS_INACTIVE,
    ]);

    $this->actingAs($user)
        ->delete("/finance/expense-categories/{$category->id}")
        ->assertRedirect(route('finance.expense-categories.index', absolute: false));

    $this->assertSoftDeleted('expense_categories', ['id' => $category->id]);
});

test('expense categories linked to expenses cannot be archived', function () {
    $user = User::factory()->create();
    $category = ExpenseCategory::create([
        'category_code' => 'EXC001',
        'category_name' => 'Travel',
        'status' => ExpenseCategory::STATUS_ACTIVE,
    ]);

    Expense::create([
        'expense_no' => 'EXP-2026-0001',
        'sequence_no' => 1,
        'expense_date' => '2026-05-23',
        'expense_category_id' => $category->id,
        'amount' => 100,
        'tax_amount' => 0,
        'total_amount' => 100,
        'status' => Expense::STATUS_DRAFT,
    ]);

    $this->actingAs($user)
        ->delete("/finance/expense-categories/{$category->id}")
        ->assertSessionHas('status', 'expense-category-delete-blocked');

    expect($category->fresh()->deleted_at)->toBeNull();
});
