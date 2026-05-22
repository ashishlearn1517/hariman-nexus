<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Settings</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Email Settings') }}</h2>
            </div>

            <a href="{{ route('settings.taxes.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                {{ __('Tax Settings') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status') === 'email-saved')
                <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __('Email settings updated successfully.') }}</div>
            @endif

            <form method="POST" action="{{ route('settings.email.update') }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="border-b border-slate-200 pb-4">
                        <h3 class="text-base font-semibold text-slate-950">{{ __('SMTP Account') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Configure the mailbox used for invoice and quotation email delivery.') }}</p>
                    </div>

                    <div class="mt-5 grid gap-5 lg:grid-cols-2">
                        <div>
                            <x-input-label for="mail_host" :value="__('Mail Host')" />
                            <x-text-input id="mail_host" name="mail_host" type="text" class="mt-2 block w-full" :value="old('mail_host', $emailSetting->mail_host)" required placeholder="smtp.gmail.com" />
                            <p class="mt-2 text-xs text-slate-500">{{ __('Examples: smtp.gmail.com, smtp.hostinger.com') }}</p>
                            <x-input-error :messages="$errors->get('mail_host')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="mail_port" :value="__('Mail Port')" />
                            <x-text-input id="mail_port" name="mail_port" type="number" min="1" max="65535" class="mt-2 block w-full" :value="old('mail_port', $emailSetting->mail_port)" required placeholder="465" />
                            <x-input-error :messages="$errors->get('mail_port')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="mail_encryption" :value="__('Mail Encryption')" />
                            <select id="mail_encryption" name="mail_encryption" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($encryptionOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('mail_encryption', $emailSetting->mail_encryption) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('mail_encryption')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="mail_username" :value="__('Sender Email ID')" />
                            <x-text-input id="mail_username" name="mail_username" type="email" class="mt-2 block w-full" :value="old('mail_username', $emailSetting->mail_username)" required placeholder="name@example.com" />
                            <x-input-error :messages="$errors->get('mail_username')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="mail_password" :value="__('Mail Password / App Password')" />
                            <x-text-input id="mail_password" name="mail_password" type="password" class="mt-2 block w-full" placeholder="{{ $emailSetting->exists && $emailSetting->mail_password ? __('Leave blank to keep saved password') : __('App password or SMTP password') }}" />
                            <p class="mt-2 text-xs text-slate-500">{{ __('For Gmail, use a Google App Password instead of your normal login password.') }}</p>
                            <x-input-error :messages="$errors->get('mail_password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="test_email_recipient" :value="__('Test Recipient Email')" />
                            <x-text-input id="test_email_recipient" name="test_email_recipient" type="email" class="mt-2 block w-full" :value="old('test_email_recipient', $emailSetting->test_email_recipient)" placeholder="recipient@example.com" />
                            <x-input-error :messages="$errors->get('test_email_recipient')" class="mt-2" />
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="border-b border-slate-200 pb-4">
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Sender Identity') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Set the From name, From address, and optional internal copy address.') }}</p>
                    </div>

                    <div class="mt-5 grid gap-5 lg:grid-cols-2">
                        <div>
                            <x-input-label for="mail_from_address" :value="__('From Email Address')" />
                            <x-text-input id="mail_from_address" name="mail_from_address" type="email" class="mt-2 block w-full" :value="old('mail_from_address', $emailSetting->mail_from_address)" required placeholder="name@example.com" />
                            <x-input-error :messages="$errors->get('mail_from_address')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="mail_from_name" :value="__('From Name')" />
                            <x-text-input id="mail_from_name" name="mail_from_name" type="text" class="mt-2 block w-full" :value="old('mail_from_name', $emailSetting->mail_from_name)" required placeholder="Hariman Nexus" />
                            <x-input-error :messages="$errors->get('mail_from_name')" class="mt-2" />
                        </div>

                        <div class="lg:col-span-2">
                            <x-input-label for="mail_cc_address" :value="__('CC Email Address')" />
                            <x-text-input id="mail_cc_address" name="mail_cc_address" type="email" class="mt-2 block w-full" :value="old('mail_cc_address', $emailSetting->mail_cc_address)" placeholder="Optional" />
                            <p class="mt-2 text-xs text-slate-500">{{ __('Optional internal copy address for outgoing invoice and quotation emails.') }}</p>
                            <x-input-error :messages="$errors->get('mail_cc_address')" class="mt-2" />
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="border-b border-slate-200 pb-4">
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Email Templates') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Use placeholders such as {client_name}, {invoice_no}, {quotation_no}, {project_name}, {total_amount}, and {company_name}.') }}</p>
                    </div>

                    <div class="mt-5 grid gap-6">
                        @foreach ([
                            ['invoice_email_subject', 'invoice_email_body', 'Invoice Email'],
                            ['reminder_email_subject', 'reminder_email_body', 'Reminder Email'],
                            ['overdue_email_subject', 'overdue_email_body', 'Overdue Email'],
                            ['quotation_email_subject', 'quotation_email_body', 'Quotation Email'],
                        ] as [$subjectField, $bodyField, $label])
                            <div class="rounded-lg border border-slate-200 p-4">
                                <h4 class="text-sm font-semibold text-slate-950">{{ __($label) }}</h4>

                                <div class="mt-4 grid gap-5">
                                    <div>
                                        <x-input-label for="{{ $subjectField }}" :value="__('Subject')" />
                                        <x-text-input id="{{ $subjectField }}" name="{{ $subjectField }}" type="text" class="mt-2 block w-full" :value="old($subjectField, $emailSetting->{$subjectField})" required />
                                        <x-input-error :messages="$errors->get($subjectField)" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="{{ $bodyField }}" :value="__('Body')" />
                                        <textarea id="{{ $bodyField }}" name="{{ $bodyField }}" rows="8" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old($bodyField, $emailSetting->{$bodyField}) }}</textarea>
                                        <x-input-error :messages="$errors->get($bodyField)" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <div class="flex justify-end">
                    <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">
                        {{ __('Save Email Settings') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
