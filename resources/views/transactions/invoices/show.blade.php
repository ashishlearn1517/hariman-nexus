<x-app-layout>
    <x-slot name="header">
        <div class="no-print flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Transactions / Invoices</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ $invoice->invoice_no }}</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('transactions.invoices.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Back') }}</a>
                <button type="button" onclick="window.print()" class="rounded-md bg-[#10243f] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#18365d]">{{ __('Print') }}</button>
            </div>
        </div>
    </x-slot>

    @php
        $logoPath = $company->company_logo_web_path ?: 'assets/images/hariman-nexus-wordmark.png';
        $currencySymbol = $invoice->currency?->symbol ?: $invoice->currency?->code;
    @endphp

    <style>
        @media print {
            @page { size: A4; margin: 8mm; }
            nav, footer, .no-print, header.bg-white { display: none !important; }
            body { background: #fff !important; }
            .print-document { border: 0 !important; box-shadow: none !important; padding: 0 !important; }
        }
    </style>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <section class="print-document rounded-lg border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex justify-between gap-6 border-b border-slate-200 pb-6">
                    <img src="{{ asset($logoPath) }}" alt="Logo" class="h-auto max-h-24 w-auto max-w-xs object-contain">
                    <div class="text-right">
                        <p class="text-3xl font-bold uppercase tracking-wide text-slate-950">{{ __('Invoice') }}</p>
                        <p class="mt-3 text-sm text-slate-600"><strong>{{ __('Invoice No:') }}</strong> {{ $invoice->invoice_no }}</p>
                        <p class="text-sm text-slate-600"><strong>{{ __('Date:') }}</strong> {{ $invoice->invoice_date?->format('d M Y') }}</p>
                        <p class="text-sm text-slate-600"><strong>{{ __('Due:') }}</strong> {{ $invoice->due_date?->format('d M Y') ?: '-' }}</p>
                    </div>
                </div>

                <div class="border-b border-slate-200 py-6">
                    <h3 class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Bill To') }}</h3>
                    <p class="mt-3 font-semibold text-slate-950">{{ $invoice->client?->name }}</p>
                    <p class="whitespace-pre-line text-sm text-slate-600">{{ $invoice->client?->client_code }}<br>{{ $invoice->client?->address }}<br>{{ $invoice->client?->email }}<br>{{ $invoice->client?->phone }}</p>
                </div>

                @include('transactions.invoices.partials.document-items')

                @if ($invoice->termCondition)
                    <div class="mt-6 border-t border-slate-200 pt-6 text-sm leading-6 text-slate-600">
                        <h3 class="font-semibold text-slate-950">{{ $invoice->termCondition->name }}</h3>
                        <p class="mt-2 whitespace-pre-line">{{ $invoice->termCondition->content }}</p>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
