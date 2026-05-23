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
                        <input type="hidden" name="item_name[]" :value="selectedLabel(row)">
                        <input type="hidden" name="item_source_id[]" x-model="row.itemId">
                        <input
                            type="search"
                            x-model="row.itemSearch"
                            x-on:input="syncItemFromSearch(row)"
                            x-on:change="syncItemFromSearch(row)"
                            :list="`invoice-item-options-${row.key}`"
                            placeholder="{{ __('Search item...') }}"
                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                        <datalist :id="`invoice-item-options-${row.key}`">
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
