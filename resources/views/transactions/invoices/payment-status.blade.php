<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-950">{{ __('Payment Status') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $invoice->invoice_no }} · {{ $invoice->client?->name }}</p>
            </div>
            <a href="{{ route('transactions.invoices.index') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">{{ __('Back to Invoices') }}</a>
        </div>
    </x-slot>

    @php($currencySymbol = $invoice->currency?->symbol ?: $invoice->currency?->code)

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status') === 'payment-added')
                <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __('Payment entry added successfully.') }}</div>
            @endif
            @if (session('status') === 'payment-deleted')
                <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __('Payment entry removed and invoice balance recalculated.') }}</div>
            @endif

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="grid gap-4 md:grid-cols-5">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Status') }}</p>
                        <p class="mt-2 text-lg font-semibold text-slate-950">{{ $statuses[$invoice->status] ?? $invoice->status }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Total') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $currencySymbol }} {{ number_format((float) $invoice->total, 2) }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Paid') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $currencySymbol }} {{ number_format((float) $invoice->amount_paid, 2) }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Balance Due') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-rose-700">{{ $currencySymbol }} {{ number_format((float) $invoice->balance_due, 2) }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Due Date') }}</p>
                        <p class="mt-2 text-lg font-semibold text-slate-950">{{ $invoice->due_date?->format('d M Y') ?: '-' }}</p>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
                @can('manage payments')
                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="border-b border-slate-200 pb-4">
                        <h3 class="text-xl font-semibold text-slate-950">{{ __('Add Payment Entry') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Record full or partial payments, attach receipt proof, and keep payment references with the invoice.') }}</p>
                    </div>

                    <form method="POST" action="{{ route('transactions.invoices.payments.store', $invoice) }}" enctype="multipart/form-data" class="mt-5 space-y-5">
                        @csrf

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="payment_date" :value="__('Payment Date')" />
                                <x-text-input id="payment_date" name="payment_date" type="date" class="mt-2 block w-full" :value="old('payment_date', now()->toDateString())" required />
                                <x-input-error :messages="$errors->get('payment_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="amount" :value="__('Amount')" />
                                <x-text-input id="amount" name="amount" type="number" min="0.01" step="0.01" class="mt-2 block w-full" :value="old('amount', $invoice->balance_due > 0 ? $invoice->balance_due : '')" required />
                                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="payment_method" :value="__('Payment Method')" />
                                <select id="payment_method" name="payment_method" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @foreach ($paymentMethods as $value => $label)
                                        <option value="{{ $value }}" @selected(old('payment_method', 'bank_transfer') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="receipt_number" :value="__('Receipt No')" />
                                <x-text-input id="receipt_number" name="receipt_number" type="text" class="mt-2 block w-full" :value="old('receipt_number')" placeholder="Optional" />
                                <x-input-error :messages="$errors->get('receipt_number')" class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="reference" :value="__('Payment Reference')" />
                                <x-text-input id="reference" name="reference" type="text" class="mt-2 block w-full" :value="old('reference')" placeholder="Bank reference, UTR, gateway ID, cheque no..." />
                                <x-input-error :messages="$errors->get('reference')" class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="receipt_file" :value="__('Receipt File')" />
                                <input id="receipt_file" name="receipt_file" type="file" accept=".pdf,.png,.jpg,.jpeg,.webp" class="mt-2 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700">
                                <x-input-error :messages="$errors->get('receipt_file')" class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="notes" :value="__('Notes')" />
                                <textarea id="notes" name="notes" rows="4" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Optional payment notes">{{ old('notes') }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">
                                {{ __('Save Payment') }}
                            </x-primary-button>
                        </div>
                    </form>
                </section>
                @endcan

                <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h3 class="text-xl font-semibold text-slate-950">{{ __('Payment Entries & Receipts') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Each entry updates paid amount, balance due, and invoice status automatically.') }}</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Date') }}</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Amount') }}</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Method') }}</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Receipt') }}</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Reference') }}</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @forelse ($invoice->payments as $payment)
                                    <tr>
                                        <td class="whitespace-nowrap px-5 py-4 font-medium text-slate-950">{{ $payment->payment_date?->format('d M Y') }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 font-semibold text-emerald-700">{{ $currencySymbol }} {{ number_format((float) $payment->amount, 2) }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $paymentMethods[$payment->payment_method] ?? $payment->payment_method }}</td>
                                        <td class="px-5 py-4">
                                            <div class="font-medium text-slate-950">{{ $payment->receipt_number ?: '-' }}</div>
                                            @if ($payment->receipt_web_path)
                                                <a href="{{ asset($payment->receipt_web_path) }}" target="_blank" class="mt-1 inline-flex rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('View Receipt') }}</a>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-slate-600">
                                            <div>{{ $payment->reference ?: '-' }}</div>
                                            @if ($payment->notes)
                                                <div class="mt-1 text-xs text-slate-500">{{ $payment->notes }}</div>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            @can('manage payments')
                                            <form method="POST" action="{{ route('transactions.invoices.payments.destroy', [$invoice, $payment]) }}" onsubmit="return confirm('Remove this payment entry?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-md bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">{{ __('Remove') }}</button>
                                            </form>
                                            @else
                                                <span class="text-xs text-slate-400">{{ __('Read only') }}</span>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No payment entries yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
