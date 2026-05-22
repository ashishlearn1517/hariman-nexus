<div class="lg:col-span-2">
    <x-input-label for="product_code" :value="__('Product Code')" />
    <div class="mt-2 flex items-center justify-between rounded-md border border-slate-200 bg-slate-50 px-4 py-3">
        <span class="text-sm font-semibold text-slate-950">{{ $product?->product_code ?? $nextProductCode }}</span>
        <span class="text-xs font-medium text-slate-500">{{ __('Assigned automatically') }}</span>
    </div>
</div>

<div>
    <x-input-label for="name" :value="__('Product Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $product?->name)" required autofocus placeholder="Example: HP 85A Cartridge" />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="unit_price" :value="__('Unit Price')" />
    <x-text-input id="unit_price" name="unit_price" type="number" step="0.01" min="0" class="mt-2 block w-full" :value="old('unit_price', $product?->unit_price)" required placeholder="0.00" />
    <x-input-error :messages="$errors->get('unit_price')" class="mt-2" />
</div>

<div>
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach ($statuses as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $product?->status ?? 'active') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</div>

<div class="lg:col-span-2">
    <x-input-label for="description" :value="__('Description')" />
    <textarea id="description" name="description" class="mt-2 block min-h-28 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Optional product details for invoice lines.">{{ old('description', $product?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>
