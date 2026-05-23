<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\NumberingSetting;
use App\Models\Project;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function expenseCategoryFixture(): ExpenseCategory
{
    return ExpenseCategory::create([
        'category_code' => 'EXC001',
        'category_name' => 'Internet',
        'status' => ExpenseCategory::STATUS_ACTIVE,
    ]);
}

test('expenses page requires authentication', function () {
    $this->get('/finance/expenses')->assertRedirect(route('login', absolute: false));
});

test('authenticated users can create filter update and archive expenses', function () {
    $user = User::factory()->create();
    Storage::fake('public');
    NumberingSetting::create(NumberingSetting::defaults());
    $category = expenseCategoryFixture();
    $project = Project::create([
        'name' => 'Finance Project',
        'start_date' => '2026-05-01',
        'status' => Project::STATUS_ACTIVE,
    ]);
    $vendor = Vendor::create([
        'vendor_code' => 'VEN001',
        'vendor_name' => 'Airtel Business',
        'status' => Vendor::STATUS_ACTIVE,
    ]);

    $this->actingAs($user)
        ->post('/finance/expenses', [
            'expense_date' => '2026-05-23',
            'expense_category_id' => $category->id,
            'project_id' => $project->id,
            'vendor_id' => $vendor->id,
            'vendor_name' => '',
            'amount' => 1000,
            'tax_amount' => 180,
            'payment_method' => 'bank_transfer',
            'receipt' => UploadedFile::fake()->image('internet-bill.jpg'),
            'notes' => 'Monthly internet',
            'status' => Expense::STATUS_PAID,
        ])
        ->assertRedirect(route('finance.expenses.index', absolute: false));

    $expense = Expense::first();

    expect($expense)
        ->expense_no->toBe('EXP-'.now()->year.'-0001')
        ->total_amount->toBe('1180.00')
        ->receipt_web_path->toStartWith('storage/expenses/'.$expense->id.'/receipt-')
        ->vendor_id->toBe($vendor->id)
        ->vendor_name->toBe('Airtel Business')
        ->created_by->toBe($user->id);

    Storage::disk('public')->assertExists($expense->receipt_path);

    $this->actingAs($user)
        ->get('/finance/expenses?search=Airtel&category_id='.$category->id.'&status=paid&date_from=2026-05-01&date_to=2026-05-31')
        ->assertOk()
        ->assertSee($expense->expense_no)
        ->assertSee('Airtel Business')
        ->assertSee('Internet');

    $this->actingAs($user)
        ->patch("/finance/expenses/{$expense->id}", [
            'expense_date' => '2026-05-24',
            'expense_category_id' => $category->id,
            'project_id' => null,
            'vendor_id' => $vendor->id,
            'vendor_name' => '',
            'amount' => 1200,
            'tax_amount' => 216,
            'payment_method' => 'credit_card',
            'receipt' => UploadedFile::fake()->create('updated-receipt.pdf', 50, 'application/pdf'),
            'notes' => 'Updated internet',
            'status' => Expense::STATUS_DRAFT,
        ])
        ->assertRedirect(route('finance.expenses.index', absolute: false));

    $this->assertDatabaseHas('expenses', [
        'id' => $expense->id,
        'vendor_name' => 'Airtel Business',
        'vendor_id' => $vendor->id,
        'total_amount' => 1416,
        'payment_method' => 'credit_card',
        'status' => Expense::STATUS_DRAFT,
    ]);

    Storage::disk('public')->assertExists($expense->fresh()->receipt_path);

    $this->actingAs($user)
        ->delete("/finance/expenses/{$expense->id}")
        ->assertRedirect(route('finance.expenses.index', absolute: false));

    $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
});

test('expense validation requires core fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/finance/expenses', [])
        ->assertSessionHasErrors([
            'expense_date',
            'expense_category_id',
            'amount',
            'tax_amount',
            'status',
        ]);
});
