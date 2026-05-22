<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Settings</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Company Setup') }}</h2>
            </div>

            <span class="rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-600">
                {{ __('Business Identity') }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status') === 'company-saved')
                <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __('Company setup updated successfully.') }}</div>
            @endif

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)]">
                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="border-b border-slate-200 pb-4">
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Company Profile') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Manage the information that appears on quotations, invoices, and business documents.') }}</p>
                    </div>

                    <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data" class="mt-5 space-y-8">
                        @csrf
                        @method('PATCH')

                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Identity') }}</h4>
                            <div class="mt-4 grid gap-5 md:grid-cols-2">
                                <div>
                                    <x-input-label for="company_name" :value="__('Company Name')" />
                                    <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $company->company_name)" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                                </div>

                                <div>
                                    <x-input-label for="company_email" :value="__('Company Email')" />
                                    <x-text-input id="company_email" name="company_email" type="email" class="mt-1 block w-full" :value="old('company_email', $company->company_email)" placeholder="name@example.com" />
                                    <x-input-error class="mt-2" :messages="$errors->get('company_email')" />
                                </div>

                                <div>
                                    <x-input-label for="website" :value="__('Website')" />
                                    <x-text-input id="website" name="website" type="url" class="mt-1 block w-full" :value="old('website', $company->website)" placeholder="https://example.com" />
                                    <x-input-error class="mt-2" :messages="$errors->get('website')" />
                                </div>

                                <div>
                                    <x-input-label for="tax_registration_number" :value="__('Tax Registration Number')" />
                                    <x-text-input id="tax_registration_number" name="tax_registration_number" type="text" class="mt-1 block w-full" :value="old('tax_registration_number', $company->tax_registration_number)" />
                                    <x-input-error class="mt-2" :messages="$errors->get('tax_registration_number')" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Phone & Location') }}</h4>
                            <div class="mt-4 grid gap-5 md:grid-cols-3">
                                <div>
                                    <x-input-label for="company_phone_country" :value="__('Phone Country')" />
                                    <select id="company_phone_country" name="company_phone_country" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">{{ __('Select country') }}</option>
                                        @foreach ($countries as $code => $country)
                                            <option value="{{ $code }}" data-phone-code="{{ $country['code'] }}" @selected(old('company_phone_country', $company->company_phone_country) === $code)>
                                                {{ $country['label'] }} ({{ $country['code'] ?: $code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('company_phone_country')" />
                                </div>

                                <div>
                                    <x-input-label for="company_phone_code" :value="__('Phone Code')" />
                                    <x-text-input id="company_phone_code" type="text" class="mt-1 block w-full bg-slate-50" :value="old('company_phone_code', $company->company_phone_code)" readonly />
                                </div>

                                <div>
                                    <x-input-label for="company_phone_local" :value="__('Phone Number')" />
                                    <x-text-input id="company_phone_local" name="company_phone_local" type="text" inputmode="tel" class="mt-1 block w-full" :value="old('company_phone_local', $company->company_phone_local)" />
                                    <x-input-error class="mt-2" :messages="$errors->get('company_phone_local')" />
                                </div>

                                <div class="md:col-span-3">
                                    <x-input-label for="company_location_country" :value="__('Company Location')" />
                                    <select id="company_location_country" name="company_location_country" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">{{ __('Select country') }}</option>
                                        @foreach ($countries as $code => $country)
                                            <option value="{{ $code }}" @selected(old('company_location_country', $company->company_location_country) === $code)>
                                                {{ $country['label'] }} ({{ $code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('company_location_country')" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Branding & Payment') }}</h4>
                            <div class="mt-4 grid gap-5 md:grid-cols-2">
                                <div>
                                    <x-input-label for="company_logo" :value="__('Company Logo')" />
                                    <input id="company_logo" name="company_logo" type="file" accept=".png,.jpg,.jpeg,.webp" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700">
                                    <x-input-error class="mt-2" :messages="$errors->get('company_logo')" />
                                </div>

                                <div>
                                    <x-input-label for="payment_qr" :value="__('Payment QR')" />
                                    <input id="payment_qr" name="payment_qr" type="file" accept=".png,.jpg,.jpeg,.webp" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700">
                                    <x-input-error class="mt-2" :messages="$errors->get('payment_qr')" />
                                </div>

                                <div>
                                    <x-input-label for="payment_label" :value="__('Payment Label')" />
                                    <x-text-input id="payment_label" name="payment_label" type="text" class="mt-1 block w-full" :value="old('payment_label', $company->payment_label)" placeholder="Payment Instructions" />
                                    <x-input-error class="mt-2" :messages="$errors->get('payment_label')" />
                                </div>

                                <div>
                                    <x-input-label for="payment_reference" :value="__('Payment Reference')" />
                                    <x-text-input id="payment_reference" name="payment_reference" type="text" class="mt-1 block w-full" :value="old('payment_reference', $company->payment_reference)" />
                                    <x-input-error class="mt-2" :messages="$errors->get('payment_reference')" />
                                </div>

                                <div class="md:col-span-2">
                                    <x-input-label for="bank_details" :value="__('Bank Details')" />
                                    <textarea id="bank_details" name="bank_details" rows="5" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Bank name&#10;Account name&#10;Account number&#10;SWIFT / IBAN">{{ old('bank_details', $company->bank_details) }}</textarea>
                                    <p class="mt-2 text-xs text-slate-500">{{ __('When bank details are filled, invoice templates can use them before QR payment instructions.') }}</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('bank_details')" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end border-t border-slate-200 pt-5">
                            <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">
                                {{ __('Save Company Setup') }}
                            </x-primary-button>
                        </div>
                    </form>
                </section>

                <aside class="space-y-6">
                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Document Preview') }}</h3>
                        <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50 p-4">
                            @if ($company->company_logo_web_path)
                                <img src="{{ asset($company->company_logo_web_path) }}" alt="{{ $company->company_name }}" class="max-h-16 max-w-full object-contain">
                            @else
                                <x-application-logo class="h-12 w-12 rounded-md object-cover" />
                            @endif

                            <div class="mt-5">
                                <div class="text-lg font-semibold text-slate-950">{{ $company->company_name }}</div>
                                <div class="mt-2 space-y-1 text-sm text-slate-600">
                                    @if ($company->company_email)
                                        <div>{{ $company->company_email }}</div>
                                    @endif
                                    @if ($company->phone())
                                        <div>{{ $company->phone() }}</div>
                                    @endif
                                    @if ($company->company_location)
                                        <div>{{ $company->company_location }}</div>
                                    @endif
                                    @if ($company->website)
                                        <div>{{ $company->website }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Payment Display') }}</h3>
                        <div class="mt-4 space-y-4 text-sm text-slate-600">
                            <div>
                                <div class="font-semibold text-slate-950">{{ $company->payment_label ?: __('Payment Instructions') }}</div>
                                <div class="mt-1 whitespace-pre-line">{{ $company->bank_details ?: __('Bank details are not configured yet.') }}</div>
                            </div>

                            @if ($company->payment_qr_web_path)
                                <img src="{{ asset($company->payment_qr_web_path) }}" alt="Payment QR" class="h-28 w-28 rounded-md border border-slate-200 bg-white object-contain p-2">
                            @endif

                            @if ($company->payment_reference)
                                <div class="rounded-md bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">{{ $company->payment_reference }}</div>
                            @endif
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const country = document.getElementById('company_phone_country');
            const phoneCode = document.getElementById('company_phone_code');
            const local = document.getElementById('company_phone_local');

            const syncPhoneCode = () => {
                const selected = country.options[country.selectedIndex];
                phoneCode.value = selected ? selected.dataset.phoneCode || '' : '';
            };

            country.addEventListener('change', syncPhoneCode);
            local.addEventListener('input', () => {
                local.value = local.value.replace(/\D+/g, '');
            });

            syncPhoneCode();
        });
    </script>
</x-app-layout>
