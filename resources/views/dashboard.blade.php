<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">{{ __('Hariman Nexus') }}</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Dashboard') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Decision view for finance, operations, and follow-ups.') }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                @can('view reports')
                    <a href="{{ route('reports.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Reports') }}</a>
                @endcan
                @can('create invoices')
                    <a href="{{ route('transactions.invoices.index') }}" class="rounded-md bg-[#10243f] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#18365d]">{{ __('New Invoice') }}</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @php
        $money = fn ($value) => number_format((float) $value, 2);
        $statusClass = fn ($status) => match ($status) {
            \App\Models\Invoice::STATUS_PAID => 'bg-emerald-50 text-emerald-700',
            \App\Models\Invoice::STATUS_OVERDUE => 'bg-rose-50 text-rose-700',
            \App\Models\Invoice::STATUS_PARTIALLY_PAID => 'bg-amber-50 text-amber-700',
            \App\Models\Invoice::STATUS_SENT => 'bg-blue-50 text-blue-700',
            default => 'bg-slate-100 text-slate-700',
        };
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section>
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Financial') }}</h3>
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ now()->format('M Y') }}</span>
                </div>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ __('Total revenue') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $money($financial['totalRevenue']) }}</p>
                        <p class="mt-4 text-sm text-slate-500">{{ __('All non-cancelled invoice value') }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ __('Outstanding') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-amber-700">{{ $money($financial['outstanding']) }}</p>
                        <p class="mt-4 text-sm text-slate-500">{{ __('Unpaid balance across open invoices') }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ __('Overdue') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-rose-700">{{ $money($financial['overdue']) }}</p>
                        <p class="mt-4 text-sm text-slate-500">{{ __('Balance past due date') }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ __('Monthly collections') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ $money($financial['monthlyCollections']) }}</p>
                        <p class="mt-4 text-sm text-slate-500">{{ __('Payments received this month') }}</p>
                    </div>
                </div>
            </section>

            <section>
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Cash Flow') }}</h3>
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Incoming vs outgoing') }}</span>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ __('Cash In') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ $money($financial['monthlyCollections']) }}</p>
                        <p class="mt-4 text-sm text-slate-500">{{ __('Invoice payments received this month') }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ __('Cash Out') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-orange-700">{{ $money($financial['monthlyExpenses']) }}</p>
                        <p class="mt-4 text-sm text-slate-500">{{ __('Expenses recorded this month') }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ __('Net Flow') }}</p>
                        <p class="mt-3 text-3xl font-semibold {{ $financial['netFlow'] >= 0 ? 'text-blue-700' : 'text-rose-700' }}">{{ $money($financial['netFlow']) }}</p>
                        <p class="mt-4 text-sm text-slate-500">{{ __('Cash in minus cash out') }}</p>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1fr_0.8fr]">
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-950">{{ __('Cash Movement') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Revenue, collections, and expenses for the last six months.') }}</p>
                        </div>
                    </div>
                    <div class="mt-5 h-72"><canvas id="dashboardCashChart"></canvas></div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Currency Exposure') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Revenue and outstanding balance by invoice currency.') }}</p>
                    <div class="mt-5 space-y-3">
                        @forelse ($financial['currencyBreakdown'] as $code => $amounts)
                            <div class="rounded-md border border-slate-200 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-semibold text-slate-950">{{ $code }}</span>
                                    <span class="text-sm font-semibold text-slate-700">{{ $money($amounts['total']) }}</span>
                                </div>
                                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-cyan-600" style="width: {{ $amounts['total'] > 0 ? min(100, ($amounts['outstanding'] / $amounts['total']) * 100) : 0 }}%"></div>
                                </div>
                                <p class="mt-2 text-xs text-slate-500">{{ __('Outstanding') }} {{ $money($amounts['outstanding']) }}</p>
                            </div>
                        @empty
                            <div class="rounded-md bg-slate-50 p-5 text-sm text-slate-500">{{ __('No invoice currency data yet.') }}</div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section>
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Operations') }}</h3>
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('This month') }}</span>
                </div>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ __('Quotations this month') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $operations['quotationsThisMonth'] }}</p>
                        <p class="mt-4 text-sm text-slate-500">{{ __('New quotation activity') }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ __('Conversion %') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-indigo-700">{{ $operations['conversionRate'] }}%</p>
                        <p class="mt-4 text-sm text-slate-500">{{ __('Quoted work converted to invoices') }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ __('Active clients') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $operations['activeClients'] }}</p>
                        <p class="mt-4 text-sm text-slate-500">{{ __('Clients available for transactions') }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ __('Active projects') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $operations['activeProjects'] }}</p>
                        <p class="mt-4 text-sm text-slate-500">{{ __('Projects available for billing') }}</p>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Quotation Pipeline') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Status mix for current quotation records.') }}</p>
                    <div class="mt-5 h-72"><canvas id="dashboardQuotationChart"></canvas></div>
                </div>

                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-950">{{ __('Recent invoices') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Latest billing activity by customer') }}</p>
                        </div>
                        @can('view invoices')
                            <a href="{{ route('transactions.invoices.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('View all') }}</a>
                        @endcan
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Invoice') }}</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Customer') }}</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Status') }}</th>
                                    <th class="px-5 py-3 text-right font-semibold text-slate-600">{{ __('Balance') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @forelse ($recentInvoices as $invoice)
                                    <tr>
                                        <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-950">{{ $invoice->invoice_no }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $invoice->client?->name }}</td>
                                        <td class="whitespace-nowrap px-5 py-4"><span class="rounded-md px-2.5 py-1 text-xs font-semibold {{ $statusClass($invoice->status) }}">{{ \App\Models\Invoice::statusOptions()[$invoice->status] ?? $invoice->status }}</span></td>
                                        <td class="whitespace-nowrap px-5 py-4 text-right text-slate-600">{{ $invoice->currency?->code }} {{ $money($invoice->balance_due) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No invoices yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section>
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Alerts') }}</h3>
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Priority follow-ups') }}</span>
                </div>
                <div class="grid gap-6 xl:grid-cols-3">
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <h4 class="font-semibold text-slate-950">{{ __('Overdue invoices') }}</h4>
                            <span class="rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">{{ $alerts['overdueInvoices']->count() }}</span>
                        </div>
                        <div class="mt-4 space-y-3">
                            @forelse ($alerts['overdueInvoices'] as $invoice)
                                <div class="rounded-md border border-rose-100 bg-rose-50/40 p-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="font-semibold text-slate-950">{{ $invoice->invoice_no }}</p>
                                        <span class="text-xs font-semibold text-rose-700">{{ $invoice->due_date?->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-slate-600">{{ $invoice->client?->name }} · {{ $invoice->currency?->code }} {{ $money($invoice->balance_due) }}</p>
                                </div>
                            @empty
                                <p class="rounded-md bg-slate-50 p-4 text-sm text-slate-500">{{ __('No overdue invoices.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <h4 class="font-semibold text-slate-950">{{ __('Invoices due soon') }}</h4>
                            <span class="rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">{{ $alerts['dueSoonInvoices']->count() }}</span>
                        </div>
                        <div class="mt-4 space-y-3">
                            @forelse ($alerts['dueSoonInvoices'] as $invoice)
                                <div class="rounded-md border border-amber-100 bg-amber-50/40 p-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="font-semibold text-slate-950">{{ $invoice->invoice_no }}</p>
                                        <span class="text-xs font-semibold text-amber-700">{{ $invoice->due_date?->format('d M') }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-slate-600">{{ $invoice->client?->name }} · {{ $invoice->currency?->code }} {{ $money($invoice->balance_due) }}</p>
                                </div>
                            @empty
                                <p class="rounded-md bg-slate-50 p-4 text-sm text-slate-500">{{ __('No invoices due in the next 7 days.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <h4 class="font-semibold text-slate-950">{{ __('Quotations expiring') }}</h4>
                            <span class="rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ $alerts['expiringQuotations']->count() }}</span>
                        </div>
                        <div class="mt-4 space-y-3">
                            @forelse ($alerts['expiringQuotations'] as $quotation)
                                <div class="rounded-md border border-blue-100 bg-blue-50/40 p-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="font-semibold text-slate-950">{{ $quotation->quotation_no }}</p>
                                        <span class="text-xs font-semibold text-blue-700">{{ $quotation->validity_date?->format('d M') }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-slate-600">{{ $quotation->client?->name }} · {{ $quotation->currency?->code }} {{ $money($quotation->total) }}</p>
                                </div>
                            @empty
                                <p class="rounded-md bg-slate-50 p-4 text-sm text-slate-500">{{ __('No quotations expiring in the next 7 days.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chartOrSkip = (id, config) => {
                const element = document.getElementById(id);
                if (! element || typeof Chart === 'undefined') return;
                new Chart(element, config);
            };

            chartOrSkip('dashboardCashChart', {
                type: 'line',
                data: {
                    labels: @js($charts['cashMonths']),
                    datasets: [
                        { label: 'Revenue', data: @js($charts['revenue']), borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.12)', fill: true, tension: 0.3 },
                        { label: 'Collections', data: @js($charts['collections']), borderColor: '#059669', backgroundColor: 'rgba(5,150,105,0.12)', fill: true, tension: 0.3 },
                        { label: 'Expenses', data: @js($charts['expenses']), borderColor: '#ea580c', backgroundColor: 'rgba(234,88,12,0.12)', fill: true, tension: 0.3 },
                    ]
                },
                options: { maintainAspectRatio: false }
            });

            chartOrSkip('dashboardQuotationChart', {
                type: 'doughnut',
                data: {
                    labels: @js(collect($charts['quotationPipeline'])->keys()->values()),
                    datasets: [{ data: @js(collect($charts['quotationPipeline'])->values()), backgroundColor: ['#64748b', '#2563eb', '#059669', '#7c3aed'] }]
                },
                options: { maintainAspectRatio: false }
            });
        });
    </script>
</x-app-layout>
