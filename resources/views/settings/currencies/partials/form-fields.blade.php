@php
    $selectedCode = old('code', $currency?->code);
    $selectedCurrency = $selectedCode ? ($currencyOptions[$selectedCode] ?? null) : null;
@endphp

<div
    x-data="{
        open: false,
        selectedCode: @js($selectedCode),
        search: @js($selectedCurrency ? $selectedCode.' - '.$selectedCurrency['name'] : ''),
        currencies: @js(collect($currencyOptions)->map(fn ($option, $code) => ['code' => $code, 'name' => $option['name'], 'symbol' => $option['symbol']])->values()),
        get filteredCurrencies() {
            const term = this.search.toLowerCase().trim();

            if (!term) {
                return this.currencies.slice(0, 40);
            }

            return this.currencies
                .filter((currency) => `${currency.code} ${currency.name} ${currency.symbol}`.toLowerCase().includes(term))
                .slice(0, 40);
        },
        selectCurrency(currency) {
            this.selectedCode = currency.code;
            this.search = `${currency.code} - ${currency.name}`;
            this.open = false;
            this.$dispatch('currency-selected', currency);
        },
        clearSelection() {
            this.selectedCode = '';
            this.search = '';
            this.$dispatch('currency-selected', { name: '', symbol: '' });
        }
    }"
    class="relative"
>
    <x-input-label for="currency_search" :value="__('Currency')" />
    <input type="hidden" id="code" name="code" x-model="selectedCode">
    <x-text-input
        id="currency_search"
        type="text"
        class="mt-2 block w-full"
        x-model="search"
        x-on:focus="open = true"
        x-on:input="selectedCode = ''; open = true; $dispatch('currency-selected', { name: '', symbol: '' })"
        x-on:keydown.escape.prevent="open = false"
        required
        autofocus
        autocomplete="off"
        placeholder="Search currency code or name"
    />

    <div
        x-show="open"
        x-on:click.outside="open = false"
        x-transition
        class="absolute z-20 mt-2 max-h-72 w-full overflow-y-auto rounded-md border border-slate-200 bg-white shadow-lg"
        style="display: none;"
    >
        <template x-if="filteredCurrencies.length === 0">
            <div class="px-4 py-3 text-sm text-slate-500">{{ __('No currencies found.') }}</div>
        </template>

        <template x-for="currency in filteredCurrencies" :key="currency.code">
            <button
                type="button"
                x-on:click="selectCurrency(currency)"
                class="flex w-full items-center justify-between gap-4 px-4 py-3 text-left text-sm hover:bg-slate-50"
            >
                <span>
                    <span class="block font-semibold text-slate-950" x-text="`${currency.code} - ${currency.name}`"></span>
                    <span class="mt-1 block text-xs text-slate-500" x-text="currency.symbol"></span>
                </span>
                <span x-show="selectedCode === currency.code" class="rounded-md bg-cyan-50 px-2 py-1 text-xs font-semibold text-cyan-700">{{ __('Selected') }}</span>
            </button>
        </template>
    </div>

    <x-input-error :messages="$errors->get('code')" class="mt-2" />
</div>

<div>
    <x-input-label for="symbol" :value="__('Symbol')" />
    <x-text-input id="symbol" type="text" maxlength="10" class="mt-2 block w-full bg-slate-50" :value="old('symbol', $currency?->symbol)" readonly placeholder="$" />
    <x-input-error :messages="$errors->get('symbol')" class="mt-2" />
</div>

<div class="lg:col-span-2">
    <x-input-label for="name" :value="__('Currency Name')" />
    <x-text-input id="name" type="text" class="mt-2 block w-full bg-slate-50" :value="old('name', $currency?->name)" readonly placeholder="US Dollar" />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="exchange_rate" :value="__('Exchange Rate')" />
    <x-text-input id="exchange_rate" name="exchange_rate" type="number" step="0.000001" min="0.000001" class="mt-2 block w-full" :value="old('exchange_rate', $currency?->exchange_rate ?? '1.000000')" required placeholder="1.000000" />
    <p class="mt-2 text-xs text-slate-500">{{ __('Use 1.000000 for your default currency. Other rates can be updated later as needed.') }}</p>
    <x-input-error :messages="$errors->get('exchange_rate')" class="mt-2" />
</div>

<div>
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach ($statuses as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $currency?->status ?? 'active') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</div>

<div class="lg:col-span-2">
    <label class="flex items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
        <input type="checkbox" name="is_default" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_default', $currency?->is_default ?? false))>
        <span>
            <span class="block text-sm font-semibold text-slate-900">{{ __('Set as default currency') }}</span>
            <span class="mt-1 block text-xs text-slate-500">{{ __('Only one default currency is allowed. Setting this currency as default will activate it and set its rate to 1.000000.') }}</span>
        </span>
    </label>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const name = document.getElementById('name');
        const symbol = document.getElementById('symbol');

        document.addEventListener('currency-selected', (event) => {
            name.value = event.detail.name || '';
            symbol.value = event.detail.symbol || '';
        });
    });
</script>
