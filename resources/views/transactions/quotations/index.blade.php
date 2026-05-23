<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Transactions</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Quotations') }}</h2>
            </div>

            <span class="rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-600">{{ __('Quote Workspace') }}</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status') === 'quotation-created')
                <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __('Quotation created successfully.') }}</div>
            @endif
            @if (session('status') === 'quotation-updated')
                <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __('Quotation updated successfully.') }}</div>
            @endif
            @if (session('status') === 'quotation-deleted')
                <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __('Quotation deleted successfully.') }}</div>
            @endif
            @if (session('status') === 'quotation-delete-blocked-converted')
                <div class="rounded-md bg-amber-50 p-4 text-sm font-medium text-amber-700">{{ __('This quotation is converted to invoice. Delete the connected invoice first, then delete the quotation.') }}</div>
            @endif
            @if (session('status') === 'quotation-sent')
                <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __('Quotation email sent successfully.') }}</div>
            @endif
            @if (session('status') === 'quotation-email-not-configured')
                <div class="rounded-md bg-amber-50 p-4 text-sm font-medium text-amber-700">{{ __('Email settings or client email are missing. Please update Email Settings and Client details first.') }}</div>
            @endif

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Create Quotation') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Build service, product, or mixed quotations and keep the quotation list on the same page.') }}</p>
                </div>

                <form method="POST" action="{{ route('transactions.quotations.store') }}" class="mt-5 space-y-6" x-data="quotationBuilder()">
                    @csrf

                    <div class="grid gap-5 lg:grid-cols-3">
                        <div>
                            <x-input-label for="quotation_date" :value="__('Quotation Date')" />
                            <x-text-input id="quotation_date" name="quotation_date" type="date" class="mt-2 block w-full" :value="old('quotation_date', now()->toDateString())" required />
                            <x-input-error :messages="$errors->get('quotation_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="validity_date" :value="__('Validity Date')" />
                            <x-text-input id="validity_date" name="validity_date" type="date" class="mt-2 block w-full" :value="old('validity_date', now()->addDays(14)->toDateString())" />
                            <x-input-error :messages="$errors->get('validity_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="currency_id" :value="__('Currency')" />
                            <select id="currency_id" name="currency_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">{{ __('Select currency') }}</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}" data-symbol="{{ $currency->symbol }}" @selected(old('currency_id') == $currency->id)>{{ $currency->code }} - {{ $currency->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('currency_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="client_id" :value="__('Client')" />
                            <select id="client_id" name="client_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">{{ __('Select client') }}</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->client_code }} - {{ $client->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="project_id" :value="__('Project')" />
                            <select id="project_id" name="project_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">{{ __('Select project') }}</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tax_setting_id" :value="__('Tax')" />
                            <select id="tax_setting_id" name="tax_setting_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-on:change="taxRate = Number($event.target.selectedOptions[0]?.dataset.rate || 0)">
                                <option value="" data-rate="0">{{ __('No tax / use client tax') }}</option>
                                @foreach ($taxes as $tax)
                                    <option value="{{ $tax->id }}" data-rate="{{ $tax->rate_percent }}" @selected(old('tax_setting_id') == $tax->id)>{{ $tax->name }} - {{ number_format((float) $tax->rate_percent, 4) }}%</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('tax_setting_id')" class="mt-2" />
                        </div>

                        <div class="lg:col-span-3">
                            <x-input-label for="term_condition_id" :value="__('Terms')" />
                            <select id="term_condition_id" name="term_condition_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('No terms') }}</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" @selected(old('term_condition_id') == $term->id)>{{ $term->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('term_condition_id')" class="mt-2" />
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200">
                        <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h4 class="text-sm font-semibold text-slate-950">{{ __('Offer Builder') }}</h4>
                                <p class="mt-1 text-xs text-slate-500">{{ __('Add service or product rows. Rates can be adjusted per quotation.') }}</p>
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
                                                    :list="`quotation-item-options-${row.key}`"
                                                    placeholder="{{ __('Search item...') }}"
                                                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    required
                                                >
                                                <datalist :id="`quotation-item-options-${row.key}`">
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

                    <div class="grid gap-4 rounded-lg border border-slate-200 bg-slate-50 p-4 sm:grid-cols-3">
                        <div><span class="block text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Subtotal') }}</span><strong class="mt-1 block text-lg text-slate-950" x-text="money(subtotal)"></strong></div>
                        <div><span class="block text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Tax') }}</span><strong class="mt-1 block text-lg text-slate-950" x-text="money(taxAmount)"></strong></div>
                        <div><span class="block text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Total') }}</span><strong class="mt-1 block text-lg text-slate-950" x-text="money(total)"></strong></div>
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">
                            {{ __('Save Quotation') }}
                        </x-primary-button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Quotation List') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Latest quotations appear first.') }}</p>
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
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Quotation') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Client') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Dates') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Total') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($quotations as $quotation)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $quotation->quotation_no }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $quotation->project?->name }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $quotation->client?->name }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $quotation->client?->client_code }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $quotation->quotation_date?->format('M d, Y') }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ __('Valid until') }} {{ $quotation->validity_date?->format('M d, Y') ?: '-' }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $quotation->currency?->symbol }} {{ number_format((float) $quotation->total, 2) }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ __('Tax') }} {{ number_format((float) $quotation->tax_amount, 2) }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $statuses[$quotation->status] ?? $quotation->status }}</span>
                                    </td>
                                    <td class="min-w-[430px] px-5 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('transactions.quotations.show', $quotation) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('View') }}</a>
                                            @can('view quotations')
                                                <a href="{{ route('transactions.quotations.status', $quotation) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('Status') }}</a>
                                            @endcan
                                            @can('edit quotations')
                                            @if ($quotation->status === \App\Models\Quotation::STATUS_DRAFT)
                                                <a href="{{ route('transactions.quotations.edit', $quotation) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('Edit') }}</a>
                                            @endif
                                            @endcan
                                            @can('create quotations')
                                            <form method="POST" action="{{ route('transactions.quotations.duplicate', $quotation) }}">
                                                @csrf
                                                <button type="submit" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('Duplicate') }}</button>
                                            </form>
                                            @endcan
                                            @can('delete quotations')
                                            @if (in_array($quotation->status, [\App\Models\Quotation::STATUS_DRAFT, \App\Models\Quotation::STATUS_REJECTED, \App\Models\Quotation::STATUS_EXPIRED, \App\Models\Quotation::STATUS_APPROVED], true))
                                                <form method="POST" action="{{ route('transactions.quotations.destroy', $quotation) }}" onsubmit="return confirm('Delete this quotation completely?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-md bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">{{ __('Delete') }}</button>
                                                </form>
                                            @elseif ($quotation->status === \App\Models\Quotation::STATUS_CONVERTED)
                                                <button type="button" onclick="alert('Converted to invoice. Delete the connected invoice first, then delete this quotation.')" class="rounded-md bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-500">{{ __('Delete') }}</button>
                                            @endif
                                            @endcan
                                            <a href="{{ route('transactions.quotations.pdf', $quotation) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('PDF') }}</a>
                                            @can('send quotations')
                                            @if (in_array($quotation->status, [\App\Models\Quotation::STATUS_DRAFT, \App\Models\Quotation::STATUS_SENT, \App\Models\Quotation::STATUS_EXPIRED], true))
                                                <form method="POST" action="{{ route('transactions.quotations.send', $quotation) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ $quotation->status === \App\Models\Quotation::STATUS_DRAFT ? __('Send') : __('Email') }}</button>
                                                </form>
                                            @endif
                                            @endcan
                                            @can('create invoices')
                                            @if ($quotation->status === \App\Models\Quotation::STATUS_APPROVED)
                                                <button type="button" class="rounded-md bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">{{ __('Convert') }}</button>
                                            @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No quotations yet. Create your first quotation above.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($quotations->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $quotations->links() }}</div>
                @endif
            </section>
        </div>
    </div>

    <script>
        function quotationBuilder() {
            return {
                rows: [{ key: Date.now(), type: 'service', itemId: '', itemSearch: '', quantity: 1, rate: 0 }],
                taxRate: Number(document.getElementById('tax_setting_id')?.selectedOptions[0]?.dataset.rate || 0),
                serviceOptions: @js($services->map(fn ($service) => ['id' => $service->id, 'label' => $service->short_name.' - '.$service->long_name, 'rate' => (float) $service->default_rate])->values()),
                productOptions: @js($products->map(fn ($product) => ['id' => $product->id, 'label' => $product->product_code.' - '.$product->name, 'rate' => (float) $product->unit_price])->values()),
                get subtotal() {
                    return this.rows.reduce((sum, row) => sum + (Number(row.quantity || 0) * Number(row.rate || 0)), 0);
                },
                get taxAmount() {
                    return this.subtotal * this.taxRate / 100;
                },
                get total() {
                    return this.subtotal + this.taxAmount;
                },
                addRow(type) {
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
                money(value) {
                    return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
            };
        }
    </script>
</x-app-layout>
