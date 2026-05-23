<div>
    <x-input-label for="vendor_name" :value="__('Vendor Name')" />
    <x-text-input id="vendor_name" name="vendor_name" type="text" class="mt-2 block w-full" :value="old('vendor_name', $vendor?->vendor_name)" required autofocus placeholder="Example: Airtel Business" />
    <x-input-error :messages="$errors->get('vendor_name')" class="mt-2" />
</div>

<div>
    <x-input-label for="contact_person" :value="__('Contact Person')" />
    <x-text-input id="contact_person" name="contact_person" type="text" class="mt-2 block w-full" :value="old('contact_person', $vendor?->contact_person)" placeholder="Optional" />
    <x-input-error :messages="$errors->get('contact_person')" class="mt-2" />
</div>

<div>
    <x-input-label for="phone" :value="__('Phone')" />
    <x-text-input id="phone" name="phone" type="text" class="mt-2 block w-full" :value="old('phone', $vendor?->phone)" placeholder="+91..." />
    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
</div>

<div>
    <x-input-label for="email" :value="__('Email')" />
    <x-text-input id="email" name="email" type="email" class="mt-2 block w-full" :value="old('email', $vendor?->email)" placeholder="vendor@example.com" />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>

<div>
    <x-input-label for="tax_number" :value="__('Tax Number')" />
    <x-text-input id="tax_number" name="tax_number" type="text" class="mt-2 block w-full" :value="old('tax_number', $vendor?->tax_number)" placeholder="GST / VAT / Tax ID" />
    <x-input-error :messages="$errors->get('tax_number')" class="mt-2" />
</div>

<div>
    <x-input-label for="payment_terms" :value="__('Payment Terms')" />
    <x-text-input id="payment_terms" name="payment_terms" type="text" class="mt-2 block w-full" :value="old('payment_terms', $vendor?->payment_terms)" placeholder="Example: Net 15, Advance, Due on receipt" />
    <x-input-error :messages="$errors->get('payment_terms')" class="mt-2" />
</div>

<div>
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach ($statuses as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $vendor?->status ?? \App\Models\Vendor::STATUS_ACTIVE) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</div>

<div class="lg:col-span-2">
    <x-input-label for="address" :value="__('Address')" />
    <textarea id="address" name="address" rows="3" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Vendor billing or office address">{{ old('address', $vendor?->address) }}</textarea>
    <x-input-error :messages="$errors->get('address')" class="mt-2" />
</div>
