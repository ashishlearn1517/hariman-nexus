<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-950">{{ __('Payment Status') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $invoice->invoice_no }}</p>
            </div>
            <a href="{{ route('transactions.invoices.index') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">{{ __('Back to Invoices') }}</a>
        </div>
    </x-slot>

    @php($currencySymbol = $invoice->currency?->symbol ?: $invoice->currency?->code)

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="grid gap-4 md:grid-cols-4">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Status') }}</p>
                        <p class="mt-2 text-lg font-semibold text-slate-950">{{ $statuses[$invoice->status] ?? $invoice->status }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Total') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $currencySymbol }} {{ number_format((float) $invoice->total, 2) }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Paid') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $currencySymbol }} {{ number_format((float) $invoice->amount_paid, 2) }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Balance Due') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-rose-700">{{ $currencySymbol }} {{ number_format((float) $invoice->balance_due, 2) }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-xl font-semibold text-slate-950">{{ __('Payment Posting') }}</h3>
                <p class="mt-3 text-sm leading-6 text-slate-500">{{ __('This page is ready for the payment workflow. Next we can add payment entries, receipts, partial payments, and automatic paid/overdue status updates.') }}</p>
            </section>
        </div>
    </div>
</x-app-layout>
