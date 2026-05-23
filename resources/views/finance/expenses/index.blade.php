<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Finance</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Expenses') }}</h2>
            </div>
            @can('view expense categories')
                <a href="{{ route('finance.expense-categories.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Expense Categories') }}</a>
            @endcan
        </div>
    </x-slot>

    @php
        $money = fn ($value) => number_format((float) $value, 2);
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Add Expense') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Record outgoing transactions with categories, project links, tax, payment method, and status.') }}</p>
                </div>

                @can('create expenses')
                    <form method="POST" action="{{ route('finance.expenses.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-5 lg:grid-cols-2">
                        @csrf
                        @include('finance.expenses.partials.form-fields', ['expense' => null])
                        <div class="flex items-end justify-start lg:justify-end">
                            <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">{{ __('Add Expense') }}</x-primary-button>
                        </div>
                    </form>
                @else
                    <p class="mt-5 text-sm text-slate-500">{{ __('You have read-only access to expenses.') }}</p>
                @endcan
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <form method="GET" action="{{ route('finance.expenses.index') }}" class="grid gap-4 lg:grid-cols-[1.2fr_1fr_1fr_1fr_1fr_auto]">
                    <div>
                        <x-input-label for="search" :value="__('Search')" />
                        <x-text-input id="search" name="search" class="mt-2 block w-full" :value="$filters['search']" placeholder="Expense no, vendor, notes..." />
                    </div>
                    <div>
                        <x-input-label for="category_id" :value="__('Category')" />
                        <select id="category_id" name="category_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected($filters['category_id'] == $category->id)>{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="status_filter" :value="__('Status')" />
                        <select id="status_filter" name="status" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="date_from" :value="__('From')" />
                        <x-text-input id="date_from" name="date_from" type="date" class="mt-2 block w-full" :value="$filters['date_from']" />
                    </div>
                    <div>
                        <x-input-label for="date_to" :value="__('To')" />
                        <x-text-input id="date_to" name="date_to" type="date" class="mt-2 block w-full" :value="$filters['date_to']" />
                    </div>
                    <div class="flex items-end gap-2">
                        <x-primary-button class="px-5 py-3">{{ __('Filter') }}</x-primary-button>
                        <a href="{{ route('finance.expenses.index') }}" class="rounded-md border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Reset') }}</a>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Expense List') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Outgoing transactions ordered by latest expense date.') }}</p>
                    </div>
                    <span class="text-sm font-medium text-slate-500">{{ $expenses->total() }} {{ Str::plural('expense', $expenses->total()) }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Expense') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Date') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Category') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Vendor') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Receipt') }}</th>
                                <th class="px-5 py-3 text-right font-semibold text-slate-600">{{ __('Total') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right font-semibold text-slate-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($expenses as $expense)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-950">{{ $expense->expense_no }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">{{ $expense->expense_date?->format('d M Y') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">{{ $expense->category?->category_name }}</td>
                                    <td class="px-5 py-4">
                                        {{ $expense->vendor?->vendor_name ?? $expense->vendor_name ?? '-' }}
                                        @if($expense->vendor)
                                            <div class="mt-1 text-xs text-slate-500">{{ $expense->vendor->vendor_code }}</div>
                                        @endif
                                        @if($expense->project)<div class="mt-1 text-xs text-slate-500">{{ $expense->project->name }}</div>@endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($expense->receipt_web_path)
                                            <a href="{{ asset($expense->receipt_web_path) }}" target="_blank" class="text-xs font-semibold text-indigo-700 hover:underline">{{ __('View') }}</a>
                                        @else
                                            <span class="text-xs text-slate-400">{{ __('None') }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-slate-950">{{ $money($expense->total_amount) }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($expense->status === \App\Models\Expense::STATUS_PAID)
                                            <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ __('Paid') }}</span>
                                        @elseif ($expense->status === \App\Models\Expense::STATUS_CANCELLED)
                                            <span class="rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">{{ __('Cancelled') }}</span>
                                        @else
                                            <span class="rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">{{ __('Draft') }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        @can('edit expenses')
                                            <a href="{{ route('finance.expenses.edit', $expense) }}" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
                                        @endcan
                                        @can('delete expenses')
                                            <form method="POST" action="{{ route('finance.expenses.destroy', $expense) }}" class="inline" onsubmit="return confirm('Archive this expense?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">{{ __('Archive') }}</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No expenses found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($expenses->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $expenses->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
