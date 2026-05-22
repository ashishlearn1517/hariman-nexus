<div>
    <x-input-label for="short_name" :value="__('Short Name')" />
    <x-text-input id="short_name" name="short_name" type="text" class="mt-2 block w-full" :value="old('short_name', $service?->short_name)" required autofocus placeholder="Example: SEO, AMC, Design" />
    <x-input-error :messages="$errors->get('short_name')" class="mt-2" />
</div>

<div>
    <x-input-label for="default_rate" :value="__('Default Rate')" />
    <x-text-input id="default_rate" name="default_rate" type="number" step="0.01" min="0" class="mt-2 block w-full" :value="old('default_rate', $service?->default_rate)" required placeholder="0.00" />
    <x-input-error :messages="$errors->get('default_rate')" class="mt-2" />
</div>

<div class="lg:col-span-2">
    <x-input-label for="long_name" :value="__('Long Name')" />
    <x-text-input id="long_name" name="long_name" type="text" class="mt-2 block w-full" :value="old('long_name', $service?->long_name)" required placeholder="Example: Search Engine Optimization Retainer" />
    <x-input-error :messages="$errors->get('long_name')" class="mt-2" />
</div>

<div>
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach ($statuses as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $service?->status ?? 'active') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</div>
