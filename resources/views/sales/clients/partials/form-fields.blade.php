<div>
    <x-input-label for="name" :value="__('Client Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $client?->name)" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="project_id" :value="__('Project')" />
    <select id="project_id" name="project_id" required class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">{{ __('Select Project') }}</option>
        @foreach ($projects as $project)
            <option value="{{ $project->id }}" @selected((string) old('project_id', $client?->project_id) === (string) $project->id)>{{ $project->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
</div>

<div>
    <x-input-label for="client_type" :value="__('Client Type')" />
    <select id="client_type" name="client_type" required class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">{{ __('Select Type') }}</option>
        @foreach ($types as $value => $label)
            <option value="{{ $value }}" @selected(old('client_type', $client?->client_type) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('client_type')" class="mt-2" />
</div>

<div>
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach ($statuses as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $client?->status ?? 'active') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</div>

<div>
    <x-input-label for="email" :value="__('Email')" />
    <x-text-input id="email" name="email" type="email" class="mt-2 block w-full" :value="old('email', $client?->email)" required />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>

<div>
    <x-input-label for="phone" :value="__('Phone')" />
    <x-text-input id="phone" name="phone" type="text" inputmode="tel" class="mt-2 block w-full" :value="old('phone', $client?->phone)" required placeholder="+1234567890" />
    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
</div>

<div class="lg:col-span-2">
    <x-input-label for="address" :value="__('Address')" />
    <textarea id="address" name="address" required class="mt-2 block min-h-28 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $client?->address) }}</textarea>
    <x-input-error :messages="$errors->get('address')" class="mt-2" />
</div>

<div class="rounded-md border border-slate-200 bg-slate-50 p-4">
    <label for="tax_applicable" class="inline-flex items-center text-sm font-medium text-slate-700">
        <input id="tax_applicable" type="checkbox" name="tax_applicable" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('tax_applicable', $client?->tax_applicable))>
        <span class="ms-2">{{ __('Tax Applicable') }}</span>
    </label>
</div>

<div>
    <x-input-label for="tax_percent" :value="__('Tax %')" />
    <x-text-input id="tax_percent" name="tax_percent" type="number" step="0.01" min="0" max="100" class="mt-2 block w-full" :value="old('tax_percent', $client?->tax_percent)" />
    <x-input-error :messages="$errors->get('tax_percent')" class="mt-2" />
</div>
