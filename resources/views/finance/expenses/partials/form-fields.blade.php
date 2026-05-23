<div>
    <x-input-label for="expense_date" :value="__('Expense Date')" />
    <x-text-input id="expense_date" name="expense_date" type="date" class="mt-2 block w-full" :value="old('expense_date', $expense?->expense_date?->toDateString() ?? now()->toDateString())" required />
    <x-input-error :messages="$errors->get('expense_date')" class="mt-2" />
</div>

<div>
    <x-input-label for="expense_category_id" :value="__('Category')" />
    <select id="expense_category_id" name="expense_category_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <option value="">{{ __('Select category') }}</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('expense_category_id', $expense?->expense_category_id) === (string) $category->id)>{{ $category->category_code }} - {{ $category->category_name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('expense_category_id')" class="mt-2" />
</div>

<div>
    <x-input-label for="project_id" :value="__('Project')" />
    <select id="project_id" name="project_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">{{ __('No project') }}</option>
        @foreach ($projects as $project)
            <option value="{{ $project->id }}" @selected((string) old('project_id', $expense?->project_id) === (string) $project->id)>{{ $project->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
</div>

<div>
    <x-input-label for="vendor_id" :value="__('Vendor')" />
    <select id="vendor_id" name="vendor_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">{{ __('No vendor / manual entry') }}</option>
        @foreach ($vendors as $vendor)
            <option value="{{ $vendor->id }}" @selected((string) old('vendor_id', $expense?->vendor_id) === (string) $vendor->id)>{{ $vendor->vendor_code }} - {{ $vendor->vendor_name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('vendor_id')" class="mt-2" />
</div>

<div>
    <x-input-label for="vendor_name" :value="__('Manual Vendor / Paid To')" />
    <x-text-input id="vendor_name" name="vendor_name" type="text" class="mt-2 block w-full" :value="old('vendor_name', $expense?->vendor_name)" placeholder="Used when no vendor is selected" />
    <x-input-error :messages="$errors->get('vendor_name')" class="mt-2" />
</div>

<div>
    <x-input-label for="amount" :value="__('Amount')" />
    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0" class="mt-2 block w-full" :value="old('amount', $expense?->amount ?? '0.00')" required />
    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
</div>

<div>
    <x-input-label for="tax_amount" :value="__('Tax Amount')" />
    <x-text-input id="tax_amount" name="tax_amount" type="number" step="0.01" min="0" class="mt-2 block w-full" :value="old('tax_amount', $expense?->tax_amount ?? '0.00')" required />
    <x-input-error :messages="$errors->get('tax_amount')" class="mt-2" />
</div>

<div>
    <x-input-label for="payment_method" :value="__('Payment Method')" />
    <select id="payment_method" name="payment_method" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">{{ __('Select method') }}</option>
        @foreach ($paymentMethods as $value => $label)
            <option value="{{ $value }}" @selected(old('payment_method', $expense?->payment_method) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
</div>

<div>
    <x-input-label for="receipt" :value="__('Receipt Upload')" />
    <input id="receipt" name="receipt" type="file" accept=".pdf,.jpg,.jpeg,.png" class="mt-2 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    <p class="mt-1 text-xs text-slate-500">{{ __('PDF, JPG, or PNG up to 5 MB.') }}</p>
    @if ($expense?->receipt_web_path)
        <a href="{{ asset($expense->receipt_web_path) }}" target="_blank" class="mt-2 inline-flex text-xs font-semibold text-indigo-700 hover:underline">{{ __('View current receipt') }}</a>
    @endif
    <x-input-error :messages="$errors->get('receipt')" class="mt-2" />
</div>

<div>
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach ($statuses as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $expense?->status ?? \App\Models\Expense::STATUS_DRAFT) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</div>

<div class="lg:col-span-2">
    <x-input-label for="notes" :value="__('Notes')" />
    <textarea id="notes" name="notes" rows="3" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Optional expense notes">{{ old('notes', $expense?->notes) }}</textarea>
    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
</div>
