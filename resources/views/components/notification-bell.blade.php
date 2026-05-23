@php
    $status = session('status');
    $messages = [
        'company-saved' => ['success', 'Company setup saved successfully.'],
        'currency-created' => ['success', 'Currency created successfully.'],
        'currency-updated' => ['success', 'Currency updated successfully.'],
        'tax-created' => ['success', 'Tax setting created successfully.'],
        'tax-updated' => ['success', 'Tax setting updated successfully.'],
        'email-saved' => ['success', 'Email settings saved successfully.'],
        'numbering-saved' => ['success', 'Numbering settings saved successfully.'],
        'user-created' => ['success', 'User created successfully.'],
        'user-updated' => ['success', 'User updated successfully.'],
        'user-activated' => ['success', 'User activated successfully.'],
        'user-deactivated' => ['warning', 'User deactivated. They cannot log in until activated again.'],
        'user-deleted' => ['warning', 'User archived successfully.'],
        'invoice-created' => ['success', 'Invoice created successfully.'],
        'invoice-updated' => ['success', 'Invoice updated successfully.'],
        'invoice-duplicated' => ['success', 'Invoice duplicated successfully.'],
        'invoice-deleted' => ['warning', 'Invoice archived successfully.'],
        'invoice-sent' => ['success', 'Invoice sent successfully.'],
        'invoice-reminder-sent' => ['success', 'Payment reminder sent successfully.'],
        'invoice-overdue-sent' => ['success', 'Overdue email sent successfully.'],
        'invoice-reminder-not-ready' => ['warning', 'Reminder is available only 24 hours before due date.'],
        'invoice-overdue-not-ready' => ['warning', 'Overdue email is available only after the due date has passed.'],
        'invoice-email-not-configured' => ['warning', 'Email settings or client email are missing. Please update Email Settings and Client details first.'],
        'payment-added' => ['success', 'Payment entry added successfully.'],
        'payment-deleted' => ['warning', 'Payment entry archived and invoice balance recalculated.'],
        'expense-category-created' => ['success', 'Expense category created successfully.'],
        'expense-category-updated' => ['success', 'Expense category updated successfully.'],
        'expense-category-deleted' => ['warning', 'Expense category archived successfully.'],
        'expense-category-delete-blocked' => ['warning', 'Expense category cannot be archived while expenses are linked to it.'],
        'expense-created' => ['success', 'Expense created successfully.'],
        'expense-updated' => ['success', 'Expense updated successfully.'],
        'expense-deleted' => ['warning', 'Expense archived successfully.'],
        'vendor-created' => ['success', 'Vendor created successfully.'],
        'vendor-updated' => ['success', 'Vendor updated successfully.'],
        'vendor-deleted' => ['warning', 'Vendor archived successfully.'],
        'vendor-delete-blocked' => ['warning', 'Vendor cannot be archived while expenses are linked to it.'],
        'quotation-created' => ['success', 'Quotation created successfully.'],
        'quotation-updated' => ['success', 'Quotation updated successfully.'],
        'quotation-duplicated' => ['success', 'Quotation duplicated successfully.'],
        'quotation-deleted' => ['warning', 'Quotation archived successfully.'],
        'quotation-sent' => ['success', 'Quotation sent successfully.'],
        'quotation-email-not-configured' => ['warning', 'Email settings or client email are missing. Please update Email Settings and Client details first.'],
        'quotation-status-updated' => ['success', 'Quotation status updated successfully.'],
        'quotation-locked' => ['warning', 'Approved quotations are locked.'],
        'quotation-delete-blocked-converted' => ['warning', 'Converted quotation cannot be archived until the connected invoice is archived.'],
        'self-status-blocked' => ['warning', 'You cannot deactivate your own account.'],
        'self-delete-blocked' => ['warning', 'You cannot delete your own account.'],
        'self-role-blocked' => ['warning', 'You cannot remove your own Super Admin role.'],
        'last-super-admin-blocked' => ['warning', 'At least one active Super Admin must remain.'],
    ];

    $flash = $status && isset($messages[$status]) ? $messages[$status] : null;
    $alerts = collect();

    if (auth()->check()) {
        if (auth()->user()->can('view invoices')) {
            $overdueCount = \App\Models\Invoice::query()
                ->whereNot('status', \App\Models\Invoice::STATUS_CANCELLED)
                ->where('balance_due', '>', 0)
                ->whereDate('due_date', '<', now()->startOfDay())
                ->count();

            if ($overdueCount) {
                $alerts->push([
                    'type' => 'danger',
                    'title' => __('Overdue invoices'),
                    'message' => $overdueCount.' '.__('overdue invoice(s) need attention.'),
                    'href' => route('transactions.invoices.index', ['status' => \App\Models\Invoice::STATUS_OVERDUE]),
                ]);
            }

            $dueSoonCount = \App\Models\Invoice::query()
                ->whereNot('status', \App\Models\Invoice::STATUS_CANCELLED)
                ->where('balance_due', '>', 0)
                ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
                ->count();

            if ($dueSoonCount) {
                $alerts->push([
                    'type' => 'warning',
                    'title' => __('Due soon'),
                    'message' => $dueSoonCount.' '.__('invoice(s) are due within 7 days.'),
                    'href' => route('transactions.invoices.index'),
                ]);
            }
        }

        if (auth()->user()->can('view quotations')) {
            $expiringQuoteCount = \App\Models\Quotation::query()
                ->whereNotIn('status', [\App\Models\Quotation::STATUS_CONVERTED, \App\Models\Quotation::STATUS_REJECTED])
                ->whereBetween('validity_date', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
                ->count();

            if ($expiringQuoteCount) {
                $alerts->push([
                    'type' => 'info',
                    'title' => __('Expiring quotations'),
                    'message' => $expiringQuoteCount.' '.__('quotation(s) are expiring soon.'),
                    'href' => route('transactions.quotations.index'),
                ]);
            }
        }
    }

    $notificationCount = $alerts->count() + ($flash ? 1 : 0);
@endphp

<x-dropdown align="right" width="96" contentClasses="bg-white">
    <x-slot name="trigger">
        <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-md border border-transparent text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" aria-label="{{ __('Open notifications') }}">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.85 23.85 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022 23.85 23.85 0 005.455 1.31m5.714 0a3 3 0 11-5.714 0" />
            </svg>

            @if ($notificationCount)
                <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1 text-[11px] font-bold text-white">
                    {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                </span>
            @endif
        </button>
    </x-slot>

    <x-slot name="content">
        <div class="w-96 max-w-[calc(100vw-2rem)]">
            <div class="border-b border-slate-100 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-cyan-700">{{ __('Notifications') }}</p>
                <h2 class="text-sm font-semibold text-slate-950">{{ __('Alerts, reminders, and updates') }}</h2>
            </div>

            <div class="max-h-96 overflow-y-auto p-2">
                @if ($flash)
                    <div class="mb-2 rounded-md border p-3 text-sm {{ $flash[0] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-800' }}">
                        <p class="font-semibold">{{ $flash[0] === 'success' ? __('Success') : __('Warning') }}</p>
                        <p class="mt-1 text-xs leading-5">{{ __($flash[1]) }}</p>
                    </div>
                @endif

                @forelse ($alerts as $alert)
                    @php
                        $classes = [
                            'danger' => 'border-rose-200 bg-rose-50 text-rose-800 hover:bg-rose-100',
                            'warning' => 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100',
                            'info' => 'border-blue-200 bg-blue-50 text-blue-800 hover:bg-blue-100',
                        ][$alert['type']];
                    @endphp
                    <a href="{{ $alert['href'] }}" class="mb-2 block rounded-md border p-3 text-sm transition {{ $classes }}">
                        <span class="font-semibold">{{ $alert['title'] }}</span>
                        <span class="mt-1 block text-xs leading-5">{{ $alert['message'] }}</span>
                    </a>
                @empty
                    @unless ($flash)
                        <div class="px-4 py-8 text-center text-sm text-slate-500">
                            {{ __('No alerts right now.') }}
                        </div>
                    @endunless
                @endforelse
            </div>
        </div>
    </x-slot>
</x-dropdown>
