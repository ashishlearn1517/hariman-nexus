<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-950">{{ __('Quotation Status') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $quotation->quotation_no }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('transactions.quotations.show', $quotation) }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">{{ __('Printable View') }}</a>
                <a href="{{ route('transactions.quotations.index') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">{{ __('Back to Quotations') }}</a>
            </div>
        </div>
    </x-slot>

    @php
        $currencySymbol = $quotation->currency?->symbol ?: $quotation->currency?->code;
        $isLocked = $quotation->status === \App\Models\Quotation::STATUS_APPROVED;
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status') === 'quotation-status-updated')
                <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __('Quotation status updated successfully.') }}</div>
            @endif
            @if (session('status') === 'quotation-locked')
                <div class="rounded-md bg-amber-50 p-4 text-sm font-medium text-amber-700">{{ __('This quotation is approved and cannot be changed from the status page.') }}</div>
            @endif

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="grid gap-4 md:grid-cols-4">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Status') }}</p>
                        <p class="mt-2 text-lg font-semibold text-slate-950">{{ $statuses[$quotation->status] ?? $quotation->status }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Total') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $currencySymbol }} {{ number_format((float) $quotation->total, 2) }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Validity Date') }}</p>
                        <p class="mt-2 text-lg font-semibold text-slate-950">{{ $quotation->validity_date?->format('Y-m-d') ?: '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Converted Invoice') }}</p>
                        <p class="mt-2 text-lg font-semibold text-slate-950">-</p>
                    </div>
                </div>
            </section>

            <form method="POST" action="{{ route('transactions.quotations.status.update', $quotation) }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-xl font-semibold text-slate-950">{{ __('Quotation Actions') }}</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        {{ $isLocked ? __('This quotation is approved. Rates and approval actions are now locked.') : __('Update item rates if needed, then save, approve, or reject the quotation from this page.') }}
                    </p>

                    @unless ($isLocked)
                        <div class="mt-5 flex flex-wrap gap-3">
                            <button name="action" value="save" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Save Rates') }}</button>
                            <button name="action" value="approve" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Approve') }}</button>
                            <button name="action" value="reject" class="rounded-md bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">{{ __('Reject') }}</button>
                        </div>
                    @endunless
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-xl font-semibold text-slate-950">{{ __('Quoted Items') }}</h3>

                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Item') }}</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Qty') }}</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Rate') }}</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($quotation->items as $item)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-slate-950">{{ $item->item_name }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ number_format((float) $item->quantity, 2) }}</td>
                                        <td class="px-4 py-3">
                                            @if ($isLocked)
                                                <span class="font-medium text-slate-700">{{ $currencySymbol }} {{ number_format((float) $item->rate, 2) }}</span>
                                            @else
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-500">{{ $currencySymbol }}</span>
                                                    <input name="rate[{{ $item->id }}]" type="number" min="0" step="0.01" value="{{ old('rate.'.$item->id, $item->rate) }}" class="w-36 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                </div>
                                                <x-input-error :messages="$errors->get('rate.'.$item->id)" class="mt-2" />
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-slate-950">{{ $currencySymbol }} {{ number_format((float) $item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </form>
        </div>
    </div>
</x-app-layout>
