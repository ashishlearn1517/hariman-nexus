<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Transactions / Invoices</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Edit Invoice') }} {{ $invoice->invoice_no }}</h2>
            </div>

            <a href="{{ route('transactions.invoices.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Back to Invoices') }}</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status') === 'invoice-duplicated')
                <div class="mb-6 rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __('Invoice duplicated. Review and save the new invoice.') }}</div>
            @endif

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <form method="POST" action="{{ route('transactions.invoices.update', $invoice) }}" class="space-y-6" x-data="invoiceBuilder(@js($invoice->items->map(fn ($item) => ['key' => $item->id, 'type' => $item->item_type, 'itemId' => (string) $item->item_source_id, 'quantity' => (float) $item->quantity, 'rate' => (float) $item->rate])->values()))">
                    @csrf
                    @method('PATCH')

                    <div class="grid gap-5 lg:grid-cols-3">
                        <div>
                            <x-input-label for="invoice_date" :value="__('Invoice Date')" />
                            <x-text-input id="invoice_date" name="invoice_date" type="date" class="mt-2 block w-full" :value="old('invoice_date', $invoice->invoice_date?->toDateString())" required />
                        </div>
                        <div>
                            <x-input-label for="due_date" :value="__('Due Date')" />
                            <x-text-input id="due_date" name="due_date" type="date" class="mt-2 block w-full" :value="old('due_date', $invoice->due_date?->toDateString())" />
                        </div>
                        <div>
                            <x-input-label for="currency_id" :value="__('Currency')" />
                            <select id="currency_id" name="currency_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}" @selected(old('currency_id', $invoice->currency_id) == $currency->id)>{{ $currency->code }} - {{ $currency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="client_id" :value="__('Client')" />
                            <select id="client_id" name="client_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" @selected(old('client_id', $invoice->client_id) == $client->id)>{{ $client->client_code }} - {{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="project_id" :value="__('Project')" />
                            <select id="project_id" name="project_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected(old('project_id', $invoice->project_id) == $project->id)>{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="tax_setting_id" :value="__('Tax')" />
                            <select id="tax_setting_id" name="tax_setting_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-on:change="taxRate = Number($event.target.selectedOptions[0]?.dataset.rate || 0)">
                                <option value="" data-rate="0">{{ __('No tax / use client tax') }}</option>
                                @foreach ($taxes as $tax)
                                    <option value="{{ $tax->id }}" data-rate="{{ $tax->rate_percent }}" @selected(old('tax_setting_id', $invoice->tax_setting_id) == $tax->id)>{{ $tax->name }} - {{ number_format((float) $tax->rate_percent, 4) }}%</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lg:col-span-3">
                            <x-input-label for="term_condition_id" :value="__('Terms')" />
                            <select id="term_condition_id" name="term_condition_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('No terms') }}</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" @selected(old('term_condition_id', $invoice->term_condition_id) == $term->id)>{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200">
                        <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-950">{{ __('Items') }}</h3>
                                <p class="mt-1 text-xs text-slate-500">{{ __('Add, remove, and change service/product rows.') }}</p>
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
        </div>
    </div>

    @include('transactions.invoices.partials.builder-script')
</x-app-layout>
