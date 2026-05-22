@php
    $currencySymbol = $invoice->currency?->symbol ?: $invoice->currency?->code;
@endphp

<div class="overflow-x-auto py-6">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold uppercase tracking-widest text-slate-600">{{ __('Item') }}</th>
                <th class="px-4 py-3 text-left font-semibold uppercase tracking-widest text-slate-600">{{ __('Type') }}</th>
                <th class="px-4 py-3 text-right font-semibold uppercase tracking-widest text-slate-600">{{ __('Qty') }}</th>
                <th class="px-4 py-3 text-right font-semibold uppercase tracking-widest text-slate-600">{{ __('Rate') }}</th>
                <th class="px-4 py-3 text-right font-semibold uppercase tracking-widest text-slate-600">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach ($invoice->items as $item)
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-950">{{ $item->item_name }}</td>
                    <td class="px-4 py-3 capitalize text-slate-600">{{ $item->item_type }}</td>
                    <td class="px-4 py-3 text-right text-slate-600">{{ number_format((float) $item->quantity, 2) }}</td>
                    <td class="px-4 py-3 text-right text-slate-600">{{ $currencySymbol }} {{ number_format((float) $item->rate, 2) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-950">{{ $currencySymbol }} {{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="grid gap-6 border-t border-slate-200 pt-6 sm:grid-cols-[1fr_18rem]">
    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">
        <p class="font-semibold text-slate-950">{{ __('Payment') }}</p>
        <p class="mt-2">{{ __('Balance due') }}: {{ $currencySymbol }} {{ number_format((float) $invoice->balance_due, 2) }}</p>
    </div>
    <div class="space-y-3 text-sm">
        <div class="flex justify-between gap-4 text-slate-600"><span>{{ __('Subtotal') }}</span><strong class="text-slate-950">{{ $currencySymbol }} {{ number_format((float) $invoice->subtotal, 2) }}</strong></div>
        <div class="flex justify-between gap-4 text-slate-600"><span>{{ __('Tax') }} ({{ number_format((float) $invoice->tax_rate_percent, 4) }}%)</span><strong class="text-slate-950">{{ $currencySymbol }} {{ number_format((float) $invoice->tax_amount, 2) }}</strong></div>
        <div class="flex justify-between gap-4 text-slate-600"><span>{{ __('Paid') }}</span><strong class="text-slate-950">{{ $currencySymbol }} {{ number_format((float) $invoice->amount_paid, 2) }}</strong></div>
        <div class="flex justify-between gap-4 border-t border-slate-200 pt-3 text-lg"><span class="font-semibold text-slate-950">{{ __('Balance') }}</span><strong class="text-slate-950">{{ $currencySymbol }} {{ number_format((float) $invoice->balance_due, 2) }}</strong></div>
    </div>
</div>
