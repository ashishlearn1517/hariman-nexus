<x-app-layout>
    <x-slot name="header">
        <div class="no-print flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Transactions / Quotations</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ $quotation->quotation_no }}</h2>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('transactions.quotations.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    {{ __('Back') }}
                </a>
                <button type="button" onclick="window.print()" class="rounded-md bg-[#10243f] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#18365d]">
                    {{ __('Print') }}
                </button>
            </div>
        </div>
    </x-slot>

    @php
        $logoPath = $company->company_logo_web_path ?: 'assets/images/hariman-nexus-wordmark.png';
        $currencySymbol = $quotation->currency?->symbol ?: $quotation->currency?->code;
        $companyPhone = $company->phone();
        $companyLocation = trim(collect([$company->company_location, $company->company_location_country])->filter()->implode(', '));
    @endphp

    <style>
        @media print {
            @page { size: A4; margin: 8mm; }
            body { background: #fff !important; }
            nav, footer, .no-print, header.bg-white { display: none !important; }
            main, .py-8, .print-shell { padding: 0 !important; margin: 0 !important; }
            .print-document {
                border: 0 !important;
                box-shadow: none !important;
                max-width: none !important;
                padding: 0 !important;
            }
            .print-compact {
                margin-top: 1rem !important;
                padding-top: 1rem !important;
            }
        }
    </style>

    <div class="print-shell py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <section class="print-document rounded-lg border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex flex-col gap-6 border-b border-slate-200 pb-6 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <img src="{{ asset($logoPath) }}" alt="{{ $company->company_name ?: config('app.name') }}" class="h-auto max-h-24 w-auto max-w-xs object-contain">
                        <div class="no-print mt-4 space-y-1 text-sm text-slate-600">
                            <p class="font-semibold text-slate-950">{{ $company->company_name ?: config('app.name', 'Hariman Nexus') }}</p>
                            @if ($company->company_email)
                                <p>{{ $company->company_email }}</p>
                            @endif
                            @if ($companyPhone)
                                <p>{{ $companyPhone }}</p>
                            @endif
                            @if ($companyLocation)
                                <p>{{ $companyLocation }}</p>
                            @endif
                            @if ($company->website)
                                <p>{{ $company->website }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="text-left sm:text-right">
                        <p class="text-3xl font-bold uppercase tracking-wide text-slate-950">{{ __('Quotation') }}</p>
                        <div class="mt-4 space-y-2 text-sm text-slate-600">
                            <p><span class="font-semibold text-slate-900">{{ __('Quotation No:') }}</span> {{ $quotation->quotation_no }}</p>
                            <p><span class="font-semibold text-slate-900">{{ __('Date:') }}</span> {{ $quotation->quotation_date?->format('d M Y') }}</p>
                            <p><span class="font-semibold text-slate-900">{{ __('Valid Until:') }}</span> {{ $quotation->validity_date?->format('d M Y') ?: '-' }}</p>
                            <p class="no-print">
                                <span class="font-semibold text-slate-900">{{ __('Status:') }}</span>
                                <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $statuses[$quotation->status] ?? $quotation->status }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 border-b border-slate-200 py-6 sm:grid-cols-2">
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Prepared For') }}</h3>
                        <div class="mt-3 space-y-1 text-sm text-slate-600">
                            <p class="text-base font-semibold text-slate-950">{{ $quotation->client?->name }}</p>
                            @if ($quotation->client?->client_code)
                                <p>{{ $quotation->client->client_code }}</p>
                            @endif
                            @if ($quotation->client?->address)
                                <p class="whitespace-pre-line">{{ $quotation->client->address }}</p>
                            @endif
                            @if ($quotation->client?->email)
                                <p>{{ $quotation->client->email }}</p>
                            @endif
                            @if ($quotation->client?->phone)
                                <p>{{ $quotation->client->phone }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="no-print sm:text-right">
                        <h3 class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Project') }}</h3>
                        <div class="mt-3 space-y-1 text-sm text-slate-600">
                            <p class="text-base font-semibold text-slate-950">{{ $quotation->project?->name ?: '-' }}</p>
                            <p>{{ __('Currency') }}: {{ $quotation->currency?->code ?: '-' }}</p>
                            @if ($quotation->taxSetting)
                                <p>{{ __('Tax') }}: {{ $quotation->taxSetting->name }} ({{ number_format((float) $quotation->tax_rate_percent, 4) }}%)</p>
                            @endif
                        </div>
                    </div>
                </div>

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
                            @foreach ($quotation->items as $item)
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
                        <p class="font-semibold text-slate-950">{{ __('Commercial Note') }}</p>
                        <p class="mt-2">{{ __('This quotation remains valid until') }} {{ $quotation->validity_date?->format('d M Y') ?: '-' }}.</p>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4 text-slate-600">
                            <span>{{ __('Subtotal') }}</span>
                            <strong class="text-slate-950">{{ $currencySymbol }} {{ number_format((float) $quotation->subtotal, 2) }}</strong>
                        </div>
                        <div class="flex justify-between gap-4 text-slate-600">
                            <span>{{ __('Tax') }} ({{ number_format((float) $quotation->tax_rate_percent, 4) }}%)</span>
                            <strong class="text-slate-950">{{ $currencySymbol }} {{ number_format((float) $quotation->tax_amount, 2) }}</strong>
                        </div>
                        <div class="flex justify-between gap-4 border-t border-slate-200 pt-3 text-lg">
                            <span class="font-semibold text-slate-950">{{ __('Total') }}</span>
                            <strong class="text-slate-950">{{ $currencySymbol }} {{ number_format((float) $quotation->total, 2) }}</strong>
                        </div>
                    </div>
                </div>

                @if ($quotation->termCondition)
                    <div class="mt-6 border-t border-slate-200 pt-6 text-sm leading-6 text-slate-600">
                        <h3 class="font-semibold text-slate-950">{{ $quotation->termCondition->name }}</h3>
                        <p class="mt-2 whitespace-pre-line">{{ $quotation->termCondition->content }}</p>
                    </div>
                @endif

                <div class="print-compact mt-8 border-t border-slate-200 pt-4 text-center text-xs text-slate-500">
                    {{ __('This is a computer-generated quotation and does not require a physical signature.') }}
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
