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
        'invoice-email-not-configured' => ['warning', 'Email settings or client email are missing.'],
        'payment-added' => ['success', 'Payment entry added successfully.'],
        'payment-deleted' => ['warning', 'Payment entry archived and invoice balance recalculated.'],
        'quotation-created' => ['success', 'Quotation created successfully.'],
        'quotation-updated' => ['success', 'Quotation updated successfully.'],
        'quotation-duplicated' => ['success', 'Quotation duplicated successfully.'],
        'quotation-deleted' => ['warning', 'Quotation archived successfully.'],
        'quotation-sent' => ['success', 'Quotation sent successfully.'],
        'quotation-status-updated' => ['success', 'Quotation status updated successfully.'],
        'quotation-locked' => ['warning', 'Approved quotations are locked.'],
        'quotation-delete-blocked-converted' => ['warning', 'Converted quotation cannot be archived until the connected invoice is archived.'],
        'self-status-blocked' => ['warning', 'You cannot deactivate your own account.'],
        'self-delete-blocked' => ['warning', 'You cannot delete your own account.'],
        'self-role-blocked' => ['warning', 'You cannot remove your own Super Admin role.'],
        'last-super-admin-blocked' => ['warning', 'At least one active Super Admin must remain.'],
    ];

    $flash = $status && isset($messages[$status]) ? $messages[$status] : null;
    $overdueCount = 0;
    $dueSoonCount = 0;
    $expiringQuoteCount = 0;

    if (auth()->check()) {
        if (auth()->user()->can('view invoices')) {
            $overdueCount = \App\Models\Invoice::query()
                ->whereNot('status', \App\Models\Invoice::STATUS_CANCELLED)
                ->where('balance_due', '>', 0)
                ->whereDate('due_date', '<', now()->startOfDay())
                ->count();

            $dueSoonCount = \App\Models\Invoice::query()
                ->whereNot('status', \App\Models\Invoice::STATUS_CANCELLED)
                ->where('balance_due', '>', 0)
                ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
                ->count();
        }

        if (auth()->user()->can('view quotations')) {
            $expiringQuoteCount = \App\Models\Quotation::query()
                ->whereNotIn('status', [\App\Models\Quotation::STATUS_CONVERTED, \App\Models\Quotation::STATUS_REJECTED])
                ->whereBetween('validity_date', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
                ->count();
        }
    }
@endphp

@if ($flash || $overdueCount || $dueSoonCount || $expiringQuoteCount)
    <div class="mx-auto max-w-7xl space-y-3 px-4 pt-4 sm:px-6 lg:px-8">
        @if ($flash)
            <div class="rounded-md border p-4 text-sm font-medium {{ $flash[0] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                {{ __($flash[1]) }}
            </div>
        @endif

        @if ($overdueCount || $dueSoonCount || $expiringQuoteCount)
            <div class="grid gap-3 md:grid-cols-3">
                @if ($overdueCount)
                    <a href="{{ route('transactions.invoices.index', ['status' => \App\Models\Invoice::STATUS_OVERDUE]) }}" class="rounded-md border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-700 hover:bg-rose-100">
                        {{ $overdueCount }} {{ __('overdue invoice(s) need attention.') }}
                    </a>
                @endif
                @if ($dueSoonCount)
                    <a href="{{ route('transactions.invoices.index') }}" class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm font-medium text-amber-700 hover:bg-amber-100">
                        {{ $dueSoonCount }} {{ __('invoice(s) are due within 7 days.') }}
                    </a>
                @endif
                @if ($expiringQuoteCount)
                    <a href="{{ route('transactions.quotations.index') }}" class="rounded-md border border-blue-200 bg-blue-50 p-4 text-sm font-medium text-blue-700 hover:bg-blue-100">
                        {{ $expiringQuoteCount }} {{ __('quotation(s) are expiring soon.') }}
                    </a>
                @endif
            </div>
        @endif
    </div>
@endif
