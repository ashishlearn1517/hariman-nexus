<div>
    <x-input-label for="name" :value="__('Tax Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $tax?->name)" required autofocus placeholder="VAT, GST, Service Tax" />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="rate_percent" :value="__('Tax Rate (%)')" />
    <x-text-input id="rate_percent" name="rate_percent" type="number" step="0.0001" min="0" max="100" class="mt-2 block w-full" :value="old('rate_percent', $tax?->rate_percent)" required placeholder="7.5000" />
    <x-input-error :messages="$errors->get('rate_percent')" class="mt-2" />
</div>

<div>
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach ($statuses as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $tax?->status ?? 'active') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</div>

<div class="lg:col-span-2">
    <x-input-label for="description" :value="__('Description')" />
    <textarea id="description" name="description" rows="4" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Where this tax should be used">{{ old('description', $tax?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="lg:col-span-2">
    <label class="flex items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
        <input type="checkbox" name="is_default" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_default', $tax?->is_default ?? false))>
        <span>
            <span class="block text-sm font-semibold text-slate-900">{{ __('Set as default tax') }}</span>
            <span class="mt-1 block text-xs text-slate-500">{{ __('Only one default tax is allowed. Setting this tax as default will automatically keep it active.') }}</span>
        </span>
    </label>
</div>
