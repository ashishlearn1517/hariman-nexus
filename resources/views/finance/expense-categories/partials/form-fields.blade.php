<div>
    <x-input-label for="category_name" :value="__('Category Name')" />
    <x-text-input id="category_name" name="category_name" type="text" class="mt-2 block w-full" :value="old('category_name', $category?->category_name)" required autofocus placeholder="Example: Internet" />
    <x-input-error :messages="$errors->get('category_name')" class="mt-2" />
</div>

<div>
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach ($statuses as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $category?->status ?? \App\Models\ExpenseCategory::STATUS_ACTIVE) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</div>

<div class="lg:col-span-2">
    <x-input-label for="description" :value="__('Description')" />
    <textarea id="description" name="description" rows="3" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Optional notes about this expense category">{{ old('description', $category?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>
