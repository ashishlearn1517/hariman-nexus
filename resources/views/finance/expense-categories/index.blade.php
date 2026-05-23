<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Finance</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Expense Categories') }}</h2>
            </div>
            @can('view expenses')
                <a href="{{ route('finance.expenses.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Expenses') }}</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Add Category') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Create clean expense groups for reporting, filtering, and finance analytics.') }}</p>
                </div>

                @can('create expense categories')
                    <form method="POST" action="{{ route('finance.expense-categories.store') }}" class="mt-5 grid gap-5 lg:grid-cols-2">
                        @csrf
                        @include('finance.expense-categories.partials.form-fields', ['category' => null])
                        <div class="flex items-end justify-start lg:justify-end">
                            <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">{{ __('Add Category') }}</x-primary-button>
                        </div>
                    </form>
                @else
                    <p class="mt-5 text-sm text-slate-500">{{ __('You have read-only access to expense categories.') }}</p>
                @endcan
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Category List') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Categories are used by the expenses module.') }}</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:items-end">
                        <span class="text-sm font-medium text-slate-500">{{ $categories->total() }} {{ Str::plural('category', $categories->total()) }}</span>
                        @include('sales.partials.table-search', ['search' => $search, 'placeholder' => __('Search categories...')])
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Code') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Category') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right font-semibold text-slate-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-950">{{ $category->category_code }}</td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $category->category_name }}</div>
                                        @if ($category->description)
                                            <div class="mt-1 max-w-xl text-xs text-slate-500">{{ Str::limit($category->description, 120) }}</div>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($category->status === \App\Models\ExpenseCategory::STATUS_ACTIVE)
                                            <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ __('Active') }}</span>
                                        @else
                                            <span class="rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        @can('edit expense categories')
                                            <a href="{{ route('finance.expense-categories.edit', $category) }}" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
                                        @endcan
                                        @can('delete expense categories')
                                            <form method="POST" action="{{ route('finance.expense-categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Archive this expense category?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">{{ __('Archive') }}</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No expense categories found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($categories->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $categories->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
