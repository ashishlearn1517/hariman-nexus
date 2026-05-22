<div class="lg:col-span-2">
    <x-input-label for="name" :value="__('Term Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $term?->name)" required autofocus placeholder="Example: Standard Payment Terms" />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="lg:col-span-2">
    <x-input-label for="content" :value="__('Term Content')" />
    <textarea id="content" name="content" required class="mt-2 block min-h-40 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Write the full term content that should appear on invoices or quotations.">{{ old('content', $term?->content) }}</textarea>
    <x-input-error :messages="$errors->get('content')" class="mt-2" />
</div>

<div>
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach ($statuses as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $term?->status ?? 'active') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</div>
