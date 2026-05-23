<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\NumberingSetting;
use App\Models\Project;
use App\Models\Vendor;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'category_id' => (string) $request->query('category_id', ''),
            'status' => (string) $request->query('status', ''),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
        ];

        return view('finance.expenses.index', [
            'expenses' => Expense::query()
                ->with(['category', 'project', 'vendor'])
                ->when($filters['search'] !== '', fn ($query) => $query->where(function ($query) use ($filters): void {
                    $query->where('expense_no', 'like', "%{$filters['search']}%")
                        ->orWhere('vendor_name', 'like', "%{$filters['search']}%")
                        ->orWhere('payment_method', 'like', "%{$filters['search']}%")
                        ->orWhere('notes', 'like', "%{$filters['search']}%")
                        ->orWhereHas('category', fn ($category) => $category->where('category_name', 'like', "%{$filters['search']}%"));
                }))
                ->when($filters['category_id'] !== '', fn ($query) => $query->where('expense_category_id', $filters['category_id']))
                ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
                ->when($filters['date_from'] !== '', fn ($query) => $query->whereDate('expense_date', '>=', $filters['date_from']))
                ->when($filters['date_to'] !== '', fn ($query) => $query->whereDate('expense_date', '<=', $filters['date_to']))
                ->latest('expense_date')
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'categories' => ExpenseCategory::query()->where('status', ExpenseCategory::STATUS_ACTIVE)->orderBy('category_name')->get(),
            'projects' => Project::query()->where('status', Project::STATUS_ACTIVE)->orderBy('name')->get(),
            'vendors' => Vendor::query()->where('status', Vendor::STATUS_ACTIVE)->orderBy('vendor_name')->get(),
            'statuses' => Expense::statusOptions(),
            'paymentMethods' => Expense::paymentMethodOptions(),
            'filters' => $filters,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $validated = $this->validatedExpense($request);
            $validated = $this->normalizeVendor($validated);
            $numbering = NumberingSetting::query()->lockForUpdate()->first() ?? NumberingSetting::create(NumberingSetting::defaults());
            [$sequence, $expenseNo] = $this->nextExpenseNumber($numbering);

            $expense = Expense::create(array_merge($validated, [
                'expense_no' => $expenseNo,
                'sequence_no' => $sequence,
                'total_amount' => (float) $validated['amount'] + (float) $validated['tax_amount'],
                'created_by' => auth()->id(),
            ]));

            if ($request->hasFile('receipt')) {
                $expense->update($this->storeReceipt($request, $expense));
            }

            $numbering->update([
                'next_expense_number' => max($numbering->next_expense_number, $sequence + 1),
            ]);

            ActivityLogger::log('expenses', 'created', auth()->user()->name.' created Expense '.$expense->expense_no.'.');
        });

        return redirect()
            ->route('finance.expenses.index')
            ->with('status', 'expense-created');
    }

    public function edit(Expense $expense): View
    {
        return view('finance.expenses.edit', [
            'expense' => $expense,
            'categories' => ExpenseCategory::query()->orderBy('category_name')->get(),
            'projects' => Project::query()->where('status', Project::STATUS_ACTIVE)->orderBy('name')->get(),
            'vendors' => Vendor::query()->orderBy('vendor_name')->get(),
            'statuses' => Expense::statusOptions(),
            'paymentMethods' => Expense::paymentMethodOptions(),
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $this->validatedExpense($request);
        $validated = $this->normalizeVendor($validated);
        $expense->update(array_merge($validated, [
            'total_amount' => (float) $validated['amount'] + (float) $validated['tax_amount'],
        ]));

        if ($request->hasFile('receipt')) {
            $expense->update($this->storeReceipt($request, $expense));
        }

        ActivityLogger::log('expenses', 'updated', auth()->user()->name.' updated Expense '.$expense->expense_no.'.');

        return redirect()
            ->route('finance.expenses.index')
            ->with('status', 'expense-updated');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expenseNo = $expense->expense_no;
        $expense->delete();
        ActivityLogger::log('expenses', 'archived', auth()->user()->name.' archived Expense '.$expenseNo.'.');

        return redirect()
            ->route('finance.expenses.index')
            ->with('status', 'expense-deleted');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedExpense(Request $request): array
    {
        return $request->validate([
            'expense_date' => ['required', 'date'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', Rule::in(array_keys(Expense::paymentMethodOptions()))],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(array_keys(Expense::statusOptions()))],
        ]);
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function nextExpenseNumber(NumberingSetting $numbering): array
    {
        $sequence = $numbering->next_expense_number;

        do {
            $expenseNo = $numbering->preview($numbering->expense_prefix, $sequence, $numbering->include_year_for_expenses);
            $exists = Expense::withTrashed()->where('expense_no', $expenseNo)->exists();

            if ($exists) {
                $sequence++;
            }
        } while ($exists);

        return [$sequence, $expenseNo];
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function normalizeVendor(array $validated): array
    {
        if (! empty($validated['vendor_id'])) {
            $vendor = Vendor::query()->find($validated['vendor_id']);
            $validated['vendor_name'] = $vendor?->vendor_name;
        }

        return $validated;
    }

    /**
     * @return array<string, string>
     */
    private function storeReceipt(Request $request, Expense $expense): array
    {
        $file = $request->file('receipt');
        $extension = $file->getClientOriginalExtension();
        $filename = 'receipt-'.now()->format('YmdHis').'-'.$expense->id.'.'.$extension;
        $path = $file->storeAs('expenses/'.$expense->id, $filename, 'public');

        return [
            'receipt_path' => $path,
            'receipt_web_path' => 'storage/'.$path,
            'receipt_original_name' => $file->getClientOriginalName(),
        ];
    }
}
