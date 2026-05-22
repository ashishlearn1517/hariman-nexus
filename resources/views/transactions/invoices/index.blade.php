<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Transactions</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Invoices') }}</h2>
            </div>

            <span class="rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-600">{{ __('Invoice Workspace') }}</span>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ quotationPickerOpen: false }">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @foreach ([
                'invoice-created' => 'Invoice created successfully.',
                'invoice-updated' => 'Invoice updated successfully.',
                'invoice-deleted' => 'Invoice deleted successfully.',
                'invoice-sent' => 'Invoice email sent successfully.',
                'invoice-email-not-configured' => 'Email settings or client email are missing. Please update Email Settings and Client details first.',
            ] as $key => $message)
                @if (session('status') === $key)
                    <div class="rounded-md {{ $key === 'invoice-email-not-configured' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }} p-4 text-sm font-medium">{{ __($message) }}</div>
                @endif
            @endforeach

            @if ($sourceQuotation)
                <div class="rounded-md bg-indigo-50 p-4 text-sm font-medium text-indigo-800">
                    {{ __('Loaded approved quotation') }} {{ $sourceQuotation->quotation_no }} {{ __('into the invoice form. Review and save to create an invoice.') }}
                </div>
            @endif

            <div x-cloak x-show="quotationPickerOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-6">
                <div x-on:click.outside="quotationPickerOpen = false" class="max-h-[85vh] w-full max-w-5xl overflow-hidden rounded-lg bg-white shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-950">{{ __('Approved Quotations') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Choose an approved quotation to load it into the invoice form.') }}</p>
                        </div>
                        <button type="button" x-on:click="quotationPickerOpen = false" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Close') }}</button>
                    </div>

                    <div class="max-h-[65vh] overflow-y-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Quotation') }}</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Client') }}</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Project') }}</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Total') }}</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($approvedQuotations as $quotation)
                                    <tr>
                                        <td class="px-5 py-4 font-semibold text-slate-950">{{ $quotation->quotation_no }}</td>
                                        <td class="px-5 py-4 text-slate-600">{{ $quotation->client?->name }}</td>
                                        <td class="px-5 py-4 text-slate-600">{{ $quotation->project?->name }}</td>
                                        <td class="px-5 py-4 font-semibold text-slate-950">{{ $quotation->currency?->symbol }} {{ number_format((float) $quotation->total, 2) }}</td>
                                        <td class="px-5 py-4">
                                            <a href="{{ route('transactions.invoices.index', ['quotation_id' => $quotation->id]) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-indigo-50 text-indigo-800 hover:bg-indigo-100" title="{{ __('Load quotation') }}">
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path d="M13.586 3.586a2 2 0 0 1 2.828 2.828l-.793.793-2.828-2.828.793-.793Z" />
                                                    <path d="m11.379 5.793 2.828 2.828-7.5 7.5a2 2 0 0 1-.878.513l-3.182.91.91-3.182a2 2 0 0 1 .513-.878l7.309-7.691Z" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No approved quotations are ready for invoicing.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Create Invoice') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Build service, product, or mixed invoices and keep the invoice list on the same page.') }}</p>
                    </div>
                    <button type="button" x-on:click="quotationPickerOpen = true" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                        {{ __('Quotation') }}
                    </button>
                </div>

                <form method="POST" action="{{ route('transactions.invoices.store') }}" class="mt-5 space-y-6" x-data="invoiceBuilder(@js($prefillRows))">
                    @csrf
                    @if ($sourceQuotation)
                        <input type="hidden" name="source_quotation_id" value="{{ $sourceQuotation->id }}">
                    @endif

                    <div class="grid gap-5 lg:grid-cols-3">
                        <div>
                            <x-input-label for="invoice_date" :value="__('Invoice Date')" />
                            <x-text-input id="invoice_date" name="invoice_date" type="date" class="mt-2 block w-full" :value="old('invoice_date', now()->toDateString())" required />
                            <x-input-error :messages="$errors->get('invoice_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="due_date" :value="__('Due Date')" />
                            <x-text-input id="due_date" name="due_date" type="date" class="mt-2 block w-full" :value="old('due_date', $sourceQuotation?->validity_date?->toDateString() ?? now()->addDays(14)->toDateString())" />
                            <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="currency_id" :value="__('Currency')" />
                            <select id="currency_id" name="currency_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">{{ __('Select currency') }}</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}" @selected(old('currency_id', $sourceQuotation?->currency_id) == $currency->id)>{{ $currency->code }} - {{ $currency->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('currency_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="client_id" :value="__('Client')" />
                            <select id="client_id" name="client_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">{{ __('Select client') }}</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" @selected(old('client_id', $sourceQuotation?->client_id) == $client->id)>{{ $client->client_code }} - {{ $client->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="project_id" :value="__('Project')" />
                            <select id="project_id" name="project_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">{{ __('Select project') }}</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected(old('project_id', $sourceQuotation?->project_id) == $project->id)>{{ $project->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tax_setting_id" :value="__('Tax')" />
                            <select id="tax_setting_id" name="tax_setting_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-on:change="taxRate = Number($event.target.selectedOptions[0]?.dataset.rate || 0)">
                                <option value="" data-rate="0">{{ __('No tax / use client tax') }}</option>
                                @foreach ($taxes as $tax)
                                    <option value="{{ $tax->id }}" data-rate="{{ $tax->rate_percent }}" @selected(old('tax_setting_id', $sourceQuotation?->tax_setting_id) == $tax->id)>{{ $tax->name }} - {{ number_format((float) $tax->rate_percent, 4) }}%</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('tax_setting_id')" class="mt-2" />
                        </div>

                        <div class="lg:col-span-3">
                            <x-input-label for="term_condition_id" :value="__('Terms')" />
                            <select id="term_condition_id" name="term_condition_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('No terms') }}</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" @selected(old('term_condition_id', $sourceQuotation?->term_condition_id) == $term->id)>{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200">
                        <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h4 class="text-sm font-semibold text-slate-950">{{ __('Invoice Builder') }}</h4>
                                <p class="mt-1 text-xs text-slate-500">{{ __('Add service or product rows. Rates can be adjusted per invoice.') }}</p>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" x-on:click="addRow('service')" class="rounded-md bg-cyan-50 px-3 py-2 text-xs font-semibold text-cyan-700 hover:bg-cyan-100">{{ __('Add Service') }}</button>
                                <button type="button" x-on:click="addRow('product')" class="rounded-md bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">{{ __('Add Product') }}</button>
                            </div>
                        </div>

                        @include('transactions.invoices.partials.item-builder-table')
                    </div>

                    @include('transactions.invoices.partials.live-summary')

                    <div class="flex justify-end">
                        <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">{{ __('Save Invoice') }}</x-primary-button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Invoice List') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Latest invoices appear first.') }}</p>
                    </div>
                    <form method="GET" class="flex gap-2">
                        <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('All Status') }}</option>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Filter') }}</button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Invoice') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Client') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Dates') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Total') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($invoices as $invoice)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $invoice->invoice_no }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $invoice->project?->name }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $invoice->client?->name }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $invoice->client?->client_code }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $invoice->invoice_date?->format('M d, Y') }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ __('Due') }} {{ $invoice->due_date?->format('M d, Y') ?: '-' }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $invoice->currency?->symbol }} {{ number_format((float) $invoice->total, 2) }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ __('Balance') }} {{ number_format((float) $invoice->balance_due, 2) }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $statuses[$invoice->status] ?? $invoice->status }}</span>
                                    </td>
                                    <td class="min-w-[430px] px-5 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('transactions.invoices.show', $invoice) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('View') }}</a>
                                            <a href="{{ route('transactions.invoices.edit', $invoice) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('Edit') }}</a>
                                            <form method="POST" action="{{ route('transactions.invoices.duplicate', $invoice) }}">@csrf<button class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('Duplicate') }}</button></form>
                                            <form method="POST" action="{{ route('transactions.invoices.destroy', $invoice) }}" onsubmit="return confirm('Delete this invoice completely?');">@csrf @method('DELETE')<button class="rounded-md bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">{{ __('Delete') }}</button></form>
                                            <a href="{{ route('transactions.invoices.pdf', $invoice) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('PDF') }}</a>
                                            <form method="POST" action="{{ route('transactions.invoices.send', $invoice) }}">@csrf<button class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('Send') }}</button></form>
                                            <a href="{{ route('transactions.invoices.payment-status', $invoice) }}" class="rounded-md bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">{{ __('Payment Status') }}</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No invoices yet. Create your first invoice above.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($invoices->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $invoices->links() }}</div>
                @endif
            </section>
        </div>
    </div>

    @include('transactions.invoices.partials.builder-script')
</x-app-layout>
