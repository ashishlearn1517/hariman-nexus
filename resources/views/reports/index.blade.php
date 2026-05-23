<x-app-layout>
    <x-slot name="header">
        <div class="no-print flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">{{ __('Hariman Nexus') }}</p>
                <h2 class="text-2xl font-semibold text-slate-950">{{ __('Reports') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Revenue, outstanding balances, expenses, client statements, collections, and quotation conversion in one place.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="window.print()" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Print') }}</button>
                <a href="{{ route('reports.index', array_merge(request()->query(), ['export' => 'pdf'])) }}" class="rounded-md bg-[#10243f] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#18365d]">{{ __('PDF Export') }}</a>
            </div>
        </div>
    </x-slot>

    @php
        $money = fn ($value) => number_format((float) $value, 2);
        $filterQuery = collect($filters)->filter(fn ($value) => $value !== '')->all();
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <style>
                @media print {
                    nav, footer, header.bg-white, .no-print { display: none !important; }
                    body { background: #fff !important; }
                    section, .shadow-sm { box-shadow: none !important; break-inside: avoid; }
                    canvas { max-height: 220px !important; }
                }
            </style>

            <section class="no-print rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <form method="GET" action="{{ route('reports.index') }}" class="grid gap-4 lg:grid-cols-[1fr_1fr_1.2fr_0.8fr_auto]">
                    <div>
                        <x-input-label for="date_from" :value="__('From')" />
                        <x-text-input id="date_from" name="date_from" type="date" class="mt-2 block w-full" :value="$filters['date_from']" />
                    </div>
                    <div>
                        <x-input-label for="date_to" :value="__('To')" />
                        <x-text-input id="date_to" name="date_to" type="date" class="mt-2 block w-full" :value="$filters['date_to']" />
                    </div>
                    <div>
                        <x-input-label for="client_id" :value="__('Client')" />
                        <select id="client_id" name="client_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('All Clients') }}</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" @selected($filters['client_id'] == $client->id)>{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="currency" :value="__('Currency')" />
                        <x-text-input id="currency" name="currency" type="text" maxlength="3" class="mt-2 block w-full uppercase" :value="$filters['currency']" placeholder="USD" />
                    </div>
                    <div class="flex items-end gap-2">
                        <x-primary-button class="px-5 py-3">{{ __('Apply') }}</x-primary-button>
                        <a href="{{ route('reports.index') }}" class="rounded-md border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Reset') }}</a>
                    </div>
                </form>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Revenue') }}</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $money($revenue['total_invoiced']) }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Total invoice value') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Collected') }}</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ $money($collections['total_collected']) }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Payments received') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Outstanding') }}</p>
                    <p class="mt-3 text-3xl font-semibold text-rose-700">{{ $money($outstanding['total_outstanding']) }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $outstanding['open_count'] }} {{ __('open invoices') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Overdue') }}</p>
                    <p class="mt-3 text-3xl font-semibold text-amber-700">{{ $money($outstanding['overdue_amount']) }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $outstanding['overdue_count'] }} {{ __('overdue invoices') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Conversion') }}</p>
                    <p class="mt-3 text-3xl font-semibold text-indigo-700">{{ $conversion['conversion_rate'] }}%</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $conversion['converted_count'] }} / {{ $conversion['quotation_count'] }} {{ __('quotes') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Expenses') }}</p>
                    <p class="mt-3 text-3xl font-semibold text-orange-700">{{ $money($expenses['total']) }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $expenses['count'] }} {{ __('expense records') }}</p>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-950">{{ __('Profit & Loss Report') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Revenue from payments minus expenses.') }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'profit_loss'])) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('CSV') }}</a>
                            <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'profit_loss_xlsx'])) }}" class="rounded-md bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">{{ __('Excel') }}</a>
                        </div>
                    </div>
                    <div class="mt-5 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-md bg-emerald-50 p-4"><p class="text-xs uppercase tracking-widest text-emerald-700">{{ __('Revenue') }}</p><p class="mt-2 text-xl font-semibold">{{ $money($profitLoss['revenue']) }}</p></div>
                        <div class="rounded-md bg-orange-50 p-4"><p class="text-xs uppercase tracking-widest text-orange-700">{{ __('Expenses') }}</p><p class="mt-2 text-xl font-semibold">{{ $money($profitLoss['expenses']) }}</p></div>
                        <div class="rounded-md {{ $profitLoss['profit'] >= 0 ? 'bg-blue-50' : 'bg-rose-50' }} p-4"><p class="text-xs uppercase tracking-widest {{ $profitLoss['profit'] >= 0 ? 'text-blue-700' : 'text-rose-700' }}">{{ __('Profit') }}</p><p class="mt-2 text-xl font-semibold">{{ $money($profitLoss['profit']) }}</p></div>
                    </div>
                    <div class="mt-5 h-64"><canvas id="profitLossChart"></canvas></div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-950">{{ __('Project Profitability') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Project revenue from invoices compared with project expenses.') }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'project_profitability'])) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('CSV') }}</a>
                            <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'project_profitability_xlsx'])) }}" class="rounded-md bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">{{ __('Excel') }}</a>
                        </div>
                    </div>
                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">{{ __('Project') }}</th><th class="px-4 py-3 text-right">{{ __('Revenue') }}</th><th class="px-4 py-3 text-right">{{ __('Expense') }}</th><th class="px-4 py-3 text-right">{{ __('Profit') }}</th></tr></thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($projectProfitability['rows']->take(8) as $row)
                                    <tr><td class="px-4 py-3 font-semibold">{{ $row['project'] }}</td><td class="px-4 py-3 text-right">{{ $money($row['revenue']) }}</td><td class="px-4 py-3 text-right">{{ $money($row['expenses']) }}</td><td class="px-4 py-3 text-right font-semibold {{ $row['profit'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">{{ $money($row['profit']) }}</td></tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">{{ __('No project profitability data found.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-950">{{ __('Monthly Expense Summary') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Outgoing transactions grouped by month.') }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'expenses'])) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('CSV') }}</a>
                            <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'expenses_xlsx'])) }}" class="rounded-md bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">{{ __('Excel') }}</a>
                        </div>
                    </div>
                    <div class="mt-5 h-72"><canvas id="expenseTrendChart"></canvas></div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-xl font-semibold text-slate-950">{{ __('Expense By Category') }}</h3>
                    <div class="mt-5 h-72"><canvas id="expenseCategoryChart"></canvas></div>
                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">{{ __('Category') }}</th><th class="px-4 py-3 text-right">{{ __('Amount') }}</th></tr></thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($expenses['by_category'] as $category => $amount)
                                    <tr><td class="px-4 py-3 font-semibold">{{ $category }}</td><td class="px-4 py-3 text-right">{{ $money($amount) }}</td></tr>
                                @empty
                                    <tr><td colspan="2" class="px-4 py-8 text-center text-slate-500">{{ __('No expense data found.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-slate-950">{{ __('Revenue Report') }}</h3>
                        <div class="flex gap-2">
                            <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'revenue'])) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('CSV') }}</a>
                            <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'revenue_xlsx'])) }}" class="rounded-md bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">{{ __('Excel') }}</a>
                        </div>
                    </div>
                    <div class="mt-5 h-72"><canvas id="revenueChart"></canvas></div>
                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">{{ __('Invoice') }}</th><th class="px-4 py-3 text-left">{{ __('Client') }}</th><th class="px-4 py-3 text-left">{{ __('Status') }}</th><th class="px-4 py-3 text-right">{{ __('Total') }}</th></tr></thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($revenue['rows']->take(5) as $invoice)
                                    <tr><td class="px-4 py-3 font-semibold">{{ $invoice->invoice_no }}</td><td class="px-4 py-3">{{ $invoice->client?->name }}</td><td class="px-4 py-3">{{ $invoice->status }}</td><td class="px-4 py-3 text-right">{{ $money($invoice->total) }}</td></tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">{{ __('No revenue data found.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-slate-950">{{ __('Outstanding Invoice Report') }}</h3>
                        <div class="flex gap-2">
                            <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'outstanding'])) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('CSV') }}</a>
                            <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'outstanding_xlsx'])) }}" class="rounded-md bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">{{ __('Excel') }}</a>
                        </div>
                    </div>
                    <div class="mt-5 h-72"><canvas id="agingChart"></canvas></div>
                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">{{ __('Invoice') }}</th><th class="px-4 py-3 text-left">{{ __('Due') }}</th><th class="px-4 py-3 text-left">{{ __('Client') }}</th><th class="px-4 py-3 text-right">{{ __('Balance') }}</th></tr></thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($outstanding['rows']->take(5) as $invoice)
                                    <tr><td class="px-4 py-3 font-semibold">{{ $invoice->invoice_no }}</td><td class="px-4 py-3">{{ $invoice->due_date?->format('d M Y') }}</td><td class="px-4 py-3">{{ $invoice->client?->name }}</td><td class="px-4 py-3 text-right">{{ $money($invoice->balance_due) }}</td></tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">{{ __('No outstanding invoices found.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-slate-950">{{ __('Client Statement') }}</h3>
                        <div class="flex gap-2">
                            <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'client_statement'])) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('CSV') }}</a>
                            <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'client_statement_xlsx'])) }}" class="rounded-md bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">{{ __('Excel') }}</a>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-md bg-slate-50 p-4"><p class="text-xs uppercase tracking-widest text-slate-500">{{ __('Debits') }}</p><p class="mt-2 text-xl font-semibold">{{ $money($clientStatement['total_debit']) }}</p></div>
                        <div class="rounded-md bg-slate-50 p-4"><p class="text-xs uppercase tracking-widest text-slate-500">{{ __('Credits') }}</p><p class="mt-2 text-xl font-semibold">{{ $money($clientStatement['total_credit']) }}</p></div>
                        <div class="rounded-md bg-slate-50 p-4"><p class="text-xs uppercase tracking-widest text-slate-500">{{ __('Balance') }}</p><p class="mt-2 text-xl font-semibold">{{ $money($clientStatement['closing_balance']) }}</p></div>
                    </div>
                    <div class="mt-5 h-64"><canvas id="clientBalanceChart"></canvas></div>
                    <div class="mt-5 max-h-80 overflow-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">{{ __('Date') }}</th><th class="px-4 py-3 text-left">{{ __('Type') }}</th><th class="px-4 py-3 text-left">{{ __('Ref') }}</th><th class="px-4 py-3 text-right">{{ __('Balance') }}</th></tr></thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($clientStatement['rows']->take(8) as $entry)
                                    <tr><td class="px-4 py-3">{{ $entry['date']?->format('d M Y') }}</td><td class="px-4 py-3">{{ $entry['type'] }}</td><td class="px-4 py-3">{{ $entry['reference'] }}</td><td class="px-4 py-3 text-right">{{ $money($entry['balance']) }}</td></tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">{{ __('Select a client or create invoice/payment activity.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-slate-950">{{ __('Payment Collection Report') }}</h3>
                        <div class="flex gap-2">
                            <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'collections'])) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('CSV') }}</a>
                            <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'collections_xlsx'])) }}" class="rounded-md bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">{{ __('Excel') }}</a>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-md bg-slate-50 p-4"><p class="text-xs uppercase tracking-widest text-slate-500">{{ __('Collected') }}</p><p class="mt-2 text-xl font-semibold">{{ $money($collections['total_collected']) }}</p></div>
                        <div class="rounded-md bg-slate-50 p-4"><p class="text-xs uppercase tracking-widest text-slate-500">{{ __('Entries') }}</p><p class="mt-2 text-xl font-semibold">{{ $collections['payment_count'] }}</p></div>
                        <div class="rounded-md bg-slate-50 p-4"><p class="text-xs uppercase tracking-widest text-slate-500">{{ __('Average') }}</p><p class="mt-2 text-xl font-semibold">{{ $money($collections['average_payment']) }}</p></div>
                    </div>
                    <div class="mt-5 h-64"><canvas id="collectionMethodChart"></canvas></div>
                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">{{ __('Date') }}</th><th class="px-4 py-3 text-left">{{ __('Invoice') }}</th><th class="px-4 py-3 text-left">{{ __('Method') }}</th><th class="px-4 py-3 text-right">{{ __('Amount') }}</th></tr></thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($collections['rows']->take(6) as $payment)
                                    <tr><td class="px-4 py-3">{{ $payment->payment_date?->format('d M Y') }}</td><td class="px-4 py-3">{{ $payment->invoice?->invoice_no }}</td><td class="px-4 py-3">{{ $payment->payment_method }}</td><td class="px-4 py-3 text-right">{{ $money($payment->amount) }}</td></tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">{{ __('No payment collections found.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-slate-950">{{ __('Quotation Conversion Report') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Monitor quoted value, approvals, and conversion into invoices.') }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'conversion'])) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('CSV') }}</a>
                        <a href="{{ route('reports.index', array_merge($filterQuery, ['export' => 'conversion_xlsx'])) }}" class="rounded-md bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">{{ __('Excel') }}</a>
                    </div>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-4">
                    <div class="rounded-md bg-slate-50 p-4"><p class="text-xs uppercase tracking-widest text-slate-500">{{ __('Quoted') }}</p><p class="mt-2 text-xl font-semibold">{{ $conversion['quotation_count'] }}</p></div>
                    <div class="rounded-md bg-slate-50 p-4"><p class="text-xs uppercase tracking-widest text-slate-500">{{ __('Approved') }}</p><p class="mt-2 text-xl font-semibold">{{ $conversion['approved_count'] }}</p></div>
                    <div class="rounded-md bg-slate-50 p-4"><p class="text-xs uppercase tracking-widest text-slate-500">{{ __('Converted') }}</p><p class="mt-2 text-xl font-semibold">{{ $conversion['converted_count'] }}</p></div>
                    <div class="rounded-md bg-slate-50 p-4"><p class="text-xs uppercase tracking-widest text-slate-500">{{ __('Converted Value') }}</p><p class="mt-2 text-xl font-semibold">{{ $money($conversion['converted_value']) }}</p></div>
                </div>
                <div class="mt-5 grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                    <div class="h-72"><canvas id="quotationStatusChart"></canvas></div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">{{ __('Quotation') }}</th><th class="px-4 py-3 text-left">{{ __('Client') }}</th><th class="px-4 py-3 text-left">{{ __('Status') }}</th><th class="px-4 py-3 text-right">{{ __('Total') }}</th></tr></thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($conversion['rows']->take(8) as $quotation)
                                    <tr><td class="px-4 py-3 font-semibold">{{ $quotation->quotation_no }}</td><td class="px-4 py-3">{{ $quotation->client?->name }}</td><td class="px-4 py-3">{{ $quotation->status }}</td><td class="px-4 py-3 text-right">{{ $money($quotation->total) }}</td></tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">{{ __('No quotations found.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const palette = ['#2563eb', '#059669', '#f59e0b', '#dc2626', '#7c3aed', '#0f766e'];
            const makeChart = (id, config) => {
                const element = document.getElementById(id);
                if (! element || typeof Chart === 'undefined') return;
                new Chart(element, config);
            };

            makeChart('revenueChart', {
                type: 'bar',
                data: {
                    labels: @js($revenue['monthly']->keys()->values()),
                    datasets: [{ label: 'Revenue', data: @js($revenue['monthly']->values()), backgroundColor: '#2563eb' }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });

            makeChart('agingChart', {
                type: 'doughnut',
                data: {
                    labels: @js(collect($outstanding['aging'])->keys()->values()),
                    datasets: [{ data: @js(collect($outstanding['aging'])->values()), backgroundColor: palette }]
                },
                options: { maintainAspectRatio: false }
            });

            makeChart('clientBalanceChart', {
                type: 'line',
                data: {
                    labels: @js($clientStatement['rows']->map(fn ($row) => $row['date']?->format('d M'))->values()),
                    datasets: [{ label: 'Balance', data: @js($clientStatement['rows']->pluck('balance')->values()), borderColor: '#0f766e', backgroundColor: 'rgba(15,118,110,0.12)', fill: true, tension: 0.3 }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });

            makeChart('collectionMethodChart', {
                type: 'bar',
                data: {
                    labels: @js($collections['by_method']->keys()->map(fn ($label) => ucwords(str_replace('_', ' ', $label)))->values()),
                    datasets: [{ label: 'Collected', data: @js($collections['by_method']->values()), backgroundColor: '#059669' }]
                },
                options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });

            makeChart('quotationStatusChart', {
                type: 'pie',
                data: {
                    labels: @js($conversion['by_status']->keys()->map(fn ($label) => ucfirst($label))->values()),
                    datasets: [{ data: @js($conversion['by_status']->values()), backgroundColor: palette }]
                },
                options: { maintainAspectRatio: false }
            });

            makeChart('expenseTrendChart', {
                type: 'line',
                data: {
                    labels: @js($expenses['monthly']->keys()->values()),
                    datasets: [{ label: 'Expenses', data: @js($expenses['monthly']->values()), borderColor: '#ea580c', backgroundColor: 'rgba(234,88,12,0.12)', fill: true, tension: 0.3 }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });

            makeChart('expenseCategoryChart', {
                type: 'bar',
                data: {
                    labels: @js($expenses['by_category']->keys()->values()),
                    datasets: [{ label: 'Amount', data: @js($expenses['by_category']->values()), backgroundColor: '#f97316' }]
                },
                options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });

            makeChart('profitLossChart', {
                type: 'bar',
                data: {
                    labels: @js($profitLoss['rows']->pluck('type')->values()),
                    datasets: [{ label: 'Amount', data: @js($profitLoss['rows']->pluck('amount')->values()), backgroundColor: ['#059669', '#f97316', '#2563eb'] }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        });
    </script>
</x-app-layout>
