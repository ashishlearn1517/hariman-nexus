<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Transactions / Quotations</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Edit Quotation') }} {{ $quotation->quotation_no }}</h2>
            </div>

            <a href="{{ route('transactions.quotations.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Back to Quotations') }}</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status') === 'quotation-duplicated')
                <div class="mb-6 rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __('Quotation duplicated. Review and save the new quotation.') }}</div>
            @endif

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <form method="POST" action="{{ route('transactions.quotations.update', $quotation) }}" class="space-y-6" x-data="quotationEditor()">
                    @csrf
                    @method('PATCH')

                    <div class="grid gap-5 lg:grid-cols-3">
                        <div>
                            <x-input-label for="quotation_date" :value="__('Quotation Date')" />
                            <x-text-input id="quotation_date" name="quotation_date" type="date" class="mt-2 block w-full" :value="old('quotation_date', $quotation->quotation_date?->toDateString())" required />
                            <x-input-error :messages="$errors->get('quotation_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="validity_date" :value="__('Validity Date')" />
                            <x-text-input id="validity_date" name="validity_date" type="date" class="mt-2 block w-full" :value="old('validity_date', $quotation->validity_date?->toDateString())" />
                            <x-input-error :messages="$errors->get('validity_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="currency_id" :value="__('Currency')" />
                            <select id="currency_id" name="currency_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">{{ __('Select currency') }}</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}" @selected(old('currency_id', $quotation->currency_id) == $currency->id)>{{ $currency->code }} - {{ $currency->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('currency_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="client_id" :value="__('Client')" />
                            <select id="client_id" name="client_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" @selected(old('client_id', $quotation->client_id) == $client->id)>{{ $client->client_code }} - {{ $client->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="project_id" :value="__('Project')" />
                            <select id="project_id" name="project_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected(old('project_id', $quotation->project_id) == $project->id)>{{ $project->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tax_setting_id" :value="__('Tax')" />
                            <select id="tax_setting_id" name="tax_setting_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('No tax / use client tax') }}</option>
                                @foreach ($taxes as $tax)
                                    <option value="{{ $tax->id }}" @selected(old('tax_setting_id', $quotation->tax_setting_id) == $tax->id)>{{ $tax->name }} - {{ number_format((float) $tax->rate_percent, 4) }}%</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('tax_setting_id')" class="mt-2" />
                        </div>

                        <div class="lg:col-span-3">
                            <x-input-label for="term_condition_id" :value="__('Terms')" />
                            <select id="term_condition_id" name="term_condition_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('No terms') }}</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" @selected(old('term_condition_id', $quotation->term_condition_id) == $term->id)>{{ $term->name }}</option>
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

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">{{ __('Type') }}</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">{{ __('Item') }}</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">{{ __('Qty') }}</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">{{ __('Rate') }}</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">{{ __('Total') }}</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    <template x-for="(row, index) in rows" :key="row.key">
                                        <tr>
                                            <td class="px-4 py-3">
                                                <select name="item_type[]" x-model="row.type" x-on:change="row.itemId = ''; row.itemSearch = ''; row.rate = 0" class="w-32 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <option value="service">{{ __('Service') }}</option>
                                                    <option value="product">{{ __('Product') }}</option>
                                                </select>
                                            </td>
                                            <td class="min-w-72 px-4 py-3">
                                                <input type="hidden" name="item_source_id[]" x-model="row.itemId">
                                                <input
                                                    type="search"
                                                    x-model="row.itemSearch"
                                                    x-on:input="syncItemFromSearch(row)"
                                                    x-on:change="syncItemFromSearch(row)"
                                                    :list="`quotation-edit-item-options-${row.key}`"
                                                    placeholder="{{ __('Search item...') }}"
                                                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    required
                                                >
                                                <datalist :id="`quotation-edit-item-options-${row.key}`">
                                                    <template x-for="item in optionsFor(row.type)" :key="`${row.type}-${item.id}`">
                                                        <option :value="item.label"></option>
                                                    </template>
                                                </datalist>
                                            </td>
                                            <td class="px-4 py-3"><input name="quantity[]" type="number" min="0.01" step="0.01" x-model.number="row.quantity" class="w-24 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required></td>
                                            <td class="px-4 py-3"><input name="rate[]" type="number" min="0" step="0.01" x-model.number="row.rate" class="w-32 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required></td>
                                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-950" x-text="money(row.quantity * row.rate)"></td>
                                            <td class="px-4 py-3 text-right"><button type="button" x-on:click="removeRow(index)" class="rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100">{{ __('Remove') }}</button></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">{{ __('Save Quotation') }}</x-primary-button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <script>
        function quotationEditor() {
            return {
                rows: @js($quotation->items->map(fn ($item) => ['key' => $item->id, 'type' => $item->item_type, 'itemId' => (string) $item->item_source_id, 'quantity' => (float) $item->quantity, 'rate' => (float) $item->rate])->values()),
                serviceOptions: @js($services->map(fn ($service) => ['id' => (string) $service->id, 'label' => $service->short_name.' - '.$service->long_name, 'rate' => (float) $service->default_rate])->values()),
                productOptions: @js($products->map(fn ($product) => ['id' => (string) $product->id, 'label' => $product->product_code.' - '.$product->name, 'rate' => (float) $product->unit_price])->values()),
                init() {
                    this.rows = this.rows.map((row) => ({ ...row, itemSearch: row.itemSearch || this.selectedLabel(row) }));
                },
                addRow(type) {
                    const list = this.optionsFor(type);
                    this.rows.push({ key: Date.now() + Math.random(), type, itemId: '', itemSearch: '', quantity: 1, rate: 0 });
                },
                removeRow(index) {
                    if (this.rows.length > 1) {
                        this.rows.splice(index, 1);
                    }
                },
                optionsFor(type) {
                    return type === 'product' ? this.productOptions : this.serviceOptions;
                },
                syncRate(row) {
                    const option = this.optionsFor(row.type).find((item) => String(item.id) === String(row.itemId));
                    row.rate = option ? option.rate : 0;
                    row.itemSearch = option ? option.label : '';
                },
                syncItemFromSearch(row) {
                    const option = this.optionsFor(row.type).find((item) => item.label.toLowerCase() === String(row.itemSearch || '').toLowerCase());
                    row.itemId = option ? String(option.id) : '';
                    row.rate = option ? option.rate : 0;
                },
                selectedLabel(row) {
                    const option = this.optionsFor(row.type).find((item) => String(item.id) === String(row.itemId));
                    return option ? option.label : '';
                },
                money(value) {
                    return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
            };
        }
    </script>
</x-app-layout>
