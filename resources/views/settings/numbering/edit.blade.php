<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Settings</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Numbering') }}</h2>
            </div>

            <a href="{{ route('settings.email.edit') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                {{ __('Email Settings') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status') === 'numbering-saved')
                <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __('Numbering settings updated successfully.') }}</div>
            @endif

            <form method="POST" action="{{ route('settings.numbering.update') }}" class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.7fr)]">
                @csrf
                @method('PATCH')

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="border-b border-slate-200 pb-4">
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Number Formats') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Set prefixes and sequence behavior for clients, products, invoices, and quotations.') }}</p>
                    </div>

                    <div class="mt-5 grid gap-5 lg:grid-cols-2">
                        <div>
                            <x-input-label for="separator" :value="__('Separator')" />
                            <x-text-input id="separator" name="separator" type="text" maxlength="3" class="mt-2 block w-full" :value="old('separator', $numbering->separator)" required />
                            <x-input-error :messages="$errors->get('separator')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="padding" :value="__('Number Padding')" />
                            <x-text-input id="padding" name="padding" type="number" min="2" max="10" class="mt-2 block w-full" :value="old('padding', $numbering->padding)" required />
                            <x-input-error :messages="$errors->get('padding')" class="mt-2" />
                        </div>

                        @foreach ([
                            ['local_client_prefix', 'Local Client Prefix'],
                            ['abroad_client_prefix', 'Abroad Client Prefix'],
                            ['product_prefix', 'Product Prefix'],
                            ['invoice_prefix', 'Invoice Prefix'],
                            ['quotation_prefix', 'Quotation Prefix'],
                        ] as [$field, $label])
                            <div>
                                <x-input-label for="{{ $field }}" :value="__($label)" />
                                <x-text-input id="{{ $field }}" name="{{ $field }}" type="text" maxlength="10" class="mt-2 block w-full uppercase" :value="old($field, $numbering->{$field})" required />
                                <x-input-error :messages="$errors->get($field)" class="mt-2" />
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 grid gap-4 border-t border-slate-200 pt-5">
                        @foreach ([
                            ['include_year_for_clients', 'Include year in client codes'],
                            ['include_year_for_invoices', 'Include year in invoice numbers'],
                            ['include_year_for_quotations', 'Include year in quotation numbers'],
                        ] as [$field, $label])
                            <label class="flex items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                                <input type="checkbox" name="{{ $field }}" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old($field, $numbering->{$field}))>
                                <span class="text-sm font-semibold text-slate-900">{{ __($label) }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>

                <aside class="space-y-6">
                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Next Numbers') }}</h3>
                        <div class="mt-5 grid gap-4">
                            @foreach ([
                                ['next_local_client_number', 'Next Local Client'],
                                ['next_abroad_client_number', 'Next Abroad Client'],
                                ['next_product_number', 'Next Product'],
                                ['next_invoice_number', 'Next Invoice'],
                                ['next_quotation_number', 'Next Quotation'],
                            ] as [$field, $label])
                                <div>
                                    <x-input-label for="{{ $field }}" :value="__($label)" />
                                    <x-text-input id="{{ $field }}" name="{{ $field }}" type="number" min="1" class="mt-2 block w-full" :value="old($field, $numbering->{$field})" required />
                                    <x-input-error :messages="$errors->get($field)" class="mt-2" />
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Current Preview') }}</h3>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between gap-3"><span class="text-slate-500">{{ __('Local Client') }}</span><strong class="text-slate-950">{{ $numbering->preview($numbering->local_client_prefix, $numbering->next_local_client_number, $numbering->include_year_for_clients) }}</strong></div>
                            <div class="flex justify-between gap-3"><span class="text-slate-500">{{ __('Abroad Client') }}</span><strong class="text-slate-950">{{ $numbering->preview($numbering->abroad_client_prefix, $numbering->next_abroad_client_number, $numbering->include_year_for_clients) }}</strong></div>
                            <div class="flex justify-between gap-3"><span class="text-slate-500">{{ __('Product') }}</span><strong class="text-slate-950">{{ $numbering->preview($numbering->product_prefix, $numbering->next_product_number) }}</strong></div>
                            <div class="flex justify-between gap-3"><span class="text-slate-500">{{ __('Invoice') }}</span><strong class="text-slate-950">{{ $numbering->preview($numbering->invoice_prefix, $numbering->next_invoice_number, $numbering->include_year_for_invoices) }}</strong></div>
                            <div class="flex justify-between gap-3"><span class="text-slate-500">{{ __('Quotation') }}</span><strong class="text-slate-950">{{ $numbering->preview($numbering->quotation_prefix, $numbering->next_quotation_number, $numbering->include_year_for_quotations) }}</strong></div>
                        </div>
                    </section>

                    <div class="flex justify-end">
                        <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">
                            {{ __('Save Numbering') }}
                        </x-primary-button>
                    </div>
                </aside>
            </form>
        </div>
    </div>
</x-app-layout>
