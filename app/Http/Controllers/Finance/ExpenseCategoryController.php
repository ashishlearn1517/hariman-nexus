<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('finance.expense-categories.index', [
            'categories' => ExpenseCategory::query()
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('category_code', 'like', "%{$search}%")
                        ->orWhere('category_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                }))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'statuses' => ExpenseCategory::statusOptions(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $category = DB::transaction(function () use ($request): ExpenseCategory {
            $category = ExpenseCategory::create(array_merge($this->validatedCategory($request), [
                'category_code' => $this->nextCategoryCode(),
                'created_by' => auth()->id(),
            ]));

            ActivityLogger::log('expense_categories', 'created', auth()->user()->name.' created Expense Category '.$category->category_code.'.');

            return $category;
        });

        return redirect()
            ->route('finance.expense-categories.index')
            ->with('status', 'expense-category-created');
    }

    public function edit(ExpenseCategory $expenseCategory): View
    {
        return view('finance.expense-categories.edit', [
            'category' => $expenseCategory,
            'statuses' => ExpenseCategory::statusOptions(),
        ]);
    }

    public function update(Request $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $expenseCategory->update($this->validatedCategory($request));
        ActivityLogger::log('expense_categories', 'updated', auth()->user()->name.' updated Expense Category '.$expenseCategory->category_code.'.');

        return redirect()
            ->route('finance.expense-categories.index')
            ->with('status', 'expense-category-updated');
    }

    public function destroy(ExpenseCategory $expenseCategory): RedirectResponse
    {
        if ($expenseCategory->expenses()->exists()) {
            return redirect()
                ->route('finance.expense-categories.index')
                ->with('status', 'expense-category-delete-blocked');
        }

        $code = $expenseCategory->category_code;
        $expenseCategory->delete();
        ActivityLogger::log('expense_categories', 'archived', auth()->user()->name.' archived Expense Category '.$code.'.');

        return redirect()
            ->route('finance.expense-categories.index')
            ->with('status', 'expense-category-deleted');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedCategory(Request $request): array
    {
        return $request->validate([
            'category_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(array_keys(ExpenseCategory::statusOptions()))],
        ]);
    }

    private function nextCategoryCode(): string
    {
        $highest = ExpenseCategory::withTrashed()
            ->select('category_code')
            ->where('category_code', 'like', 'EXC%')
            ->lockForUpdate()
            ->get()
            ->map(fn (ExpenseCategory $category) => (int) preg_replace('/\D+/', '', $category->category_code))
            ->max() ?? 0;

        return 'EXC'.str_pad((string) ($highest + 1), 3, '0', STR_PAD_LEFT);
    }
}
