<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Finance</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Vendors') }}</h2>
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
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Add Vendor') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Create supplier records for procurement, expenses, payments, and future purchase workflows.') }}</p>
                </div>

                @can('create vendors')
                    <form method="POST" action="{{ route('finance.vendors.store') }}" class="mt-5 grid gap-5 lg:grid-cols-2">
                        @csrf
                        @include('finance.vendors.partials.form-fields', ['vendor' => null])
                        <div class="flex items-end justify-start lg:justify-end">
                            <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">{{ __('Add Vendor') }}</x-primary-button>
                        </div>
                    </form>
                @else
                    <p class="mt-5 text-sm text-slate-500">{{ __('You have read-only access to vendors.') }}</p>
                @endcan
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Vendor List') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Vendor records used by expense entries.') }}</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:items-end">
                        <span class="text-sm font-medium text-slate-500">{{ $vendors->total() }} {{ Str::plural('vendor', $vendors->total()) }}</span>
                        @include('sales.partials.table-search', ['search' => $search, 'placeholder' => __('Search vendors...')])
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Vendor') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Contact') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Tax / Terms') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right font-semibold text-slate-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($vendors as $vendor)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $vendor->vendor_name }}</div>
                                        <div class="mt-1 inline-flex rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $vendor->vendor_code }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-700">{{ $vendor->contact_person ?: '-' }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $vendor->phone ?: '-' }} · {{ $vendor->email ?: '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-slate-700">{{ $vendor->tax_number ?: '-' }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $vendor->payment_terms ?: __('No payment terms') }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($vendor->status === \App\Models\Vendor::STATUS_ACTIVE)
                                            <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ __('Active') }}</span>
                                        @else
                                            <span class="rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        @can('edit vendors')
                                            <a href="{{ route('finance.vendors.edit', $vendor) }}" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
                                        @endcan
                                        @can('delete vendors')
                                            <form method="POST" action="{{ route('finance.vendors.destroy', $vendor) }}" class="inline" onsubmit="return confirm('Archive this vendor?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">{{ __('Archive') }}</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No vendors found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($vendors->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $vendors->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
