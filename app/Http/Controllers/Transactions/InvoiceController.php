<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Currency;
use App\Models\EmailSetting;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\NumberingSetting;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\TaxSetting;
use App\Models\TermCondition;
use App\Support\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = (string) $request->query('status', '');
        $sourceQuotation = null;

        if ($request->filled('quotation_id')) {
            $sourceQuotation = Quotation::query()
                ->with(['client', 'project', 'currency', 'taxSetting', 'termCondition', 'items'])
                ->where('status', Quotation::STATUS_APPROVED)
                ->find($request->integer('quotation_id'));
        }

        return view('transactions.invoices.index', array_merge($this->invoiceFormData(), [
            'invoices' => Invoice::query()
                ->with(['client', 'project', 'currency'])
                ->when($statusFilter !== '', fn ($query) => $query->where('status', $statusFilter))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'statuses' => Invoice::statusOptions(),
            'statusFilter' => $statusFilter,
            'approvedQuotations' => Quotation::query()
                ->with(['client', 'project', 'currency'])
                ->where('status', Quotation::STATUS_APPROVED)
                ->latest()
                ->get(),
            'sourceQuotation' => $sourceQuotation,
            'prefillRows' => $sourceQuotation?->items->map(fn ($item) => [
                'key' => 'quotation-'.$item->id,
                'type' => $item->item_type,
                'itemId' => $item->item_source_id ? (string) $item->item_source_id : 'quoted-'.$item->id,
                'label' => $item->item_name,
                'quantity' => (float) $item->quantity,
                'rate' => (float) $item->rate,
            ])->values(),
            'prefillServiceOptions' => $sourceQuotation?->items
                ->where('item_type', 'service')
                ->map(fn ($item) => [
                    'id' => $item->item_source_id ? (string) $item->item_source_id : 'quoted-'.$item->id,
                    'label' => $item->item_name,
                    'rate' => (float) $item->rate,
                ])
                ->values() ?? collect(),
            'prefillProductOptions' => $sourceQuotation?->items
                ->where('item_type', 'product')
                ->map(fn ($item) => [
                    'id' => $item->item_source_id ? (string) $item->item_source_id : 'quoted-'.$item->id,
                    'label' => $item->item_name,
                    'rate' => (float) $item->rate,
                ])
                ->values() ?? collect(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedInvoice($request);

        DB::transaction(function () use ($validated): void {
            $items = $this->buildItems($validated);
            $subtotal = collect($items)->sum('line_total');
            $taxRate = $this->taxRate($validated);
            $taxAmount = round($subtotal * $taxRate / 100, 2);
            $numbering = NumberingSetting::query()->lockForUpdate()->first() ?? NumberingSetting::create(NumberingSetting::defaults());
            [$sequence, $invoiceNo] = $this->nextInvoiceNumber($numbering);

            $invoice = Invoice::create([
                'invoice_no' => $invoiceNo,
                'sequence_no' => $sequence,
                'source_quotation_id' => $validated['source_quotation_id'] ?? null,
                'client_id' => $validated['client_id'],
                'project_id' => $validated['project_id'],
                'currency_id' => $validated['currency_id'],
                'tax_setting_id' => $validated['tax_setting_id'] ?? null,
                'term_condition_id' => $validated['term_condition_id'] ?? null,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_rate_percent' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'balance_due' => $subtotal + $taxAmount,
                'status' => Invoice::STATUS_DRAFT,
            ]);

            $invoice->items()->createMany($items);
            ActivityLogger::log('invoices', 'created', auth()->user()->name.' created Invoice '.$invoice->invoice_no.'.');

            if (! empty($validated['source_quotation_id'])) {
                Quotation::query()
                    ->whereKey($validated['source_quotation_id'])
                    ->where('status', Quotation::STATUS_APPROVED)
                    ->update([
                        'status' => Quotation::STATUS_CONVERTED,
                        'converted_at' => now(),
                    ]);
                ActivityLogger::log('quotations', 'converted', auth()->user()->name.' converted Quotation ID '.$validated['source_quotation_id'].' to Invoice '.$invoice->invoice_no.'.');
            }

            $numbering->update([
                'next_invoice_number' => max($numbering->next_invoice_number, $sequence + 1),
            ]);
        });

        return redirect()
            ->route('transactions.invoices.index')
            ->with('status', 'invoice-created');
    }

    public function show(Invoice $invoice): View
    {
        return view('transactions.invoices.show', [
            'invoice' => $this->loadInvoice($invoice),
            'company' => CompanySetting::current(),
            'statuses' => Invoice::statusOptions(),
        ]);
    }

    public function edit(Invoice $invoice): View
    {
        return view('transactions.invoices.edit', array_merge($this->invoiceFormData(), [
            'invoice' => $this->loadInvoice($invoice),
        ]));
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $this->validatedInvoice($request);
        $invoiceNo = $invoice->invoice_no;

        DB::transaction(function () use ($invoice, $validated): void {
            $items = $this->buildItems($validated);
            $subtotal = collect($items)->sum('line_total');
            $taxRate = $this->taxRate($validated);
            $taxAmount = round($subtotal * $taxRate / 100, 2);
            $total = $subtotal + $taxAmount;
            $amountPaid = min((float) $invoice->amount_paid, $total);

            $invoice->update([
                'client_id' => $validated['client_id'],
                'project_id' => $validated['project_id'],
                'currency_id' => $validated['currency_id'],
                'tax_setting_id' => $validated['tax_setting_id'] ?? null,
                'term_condition_id' => $validated['term_condition_id'] ?? null,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_rate_percent' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'amount_paid' => $amountPaid,
                'balance_due' => $total - $amountPaid,
            ]);

            $invoice->items()->delete();
            $invoice->items()->createMany($items);
        });
        ActivityLogger::log('invoices', 'updated', $request->user()->name.' edited Invoice '.$invoiceNo.'.');

        return redirect()
            ->route('transactions.invoices.index')
            ->with('status', 'invoice-updated');
    }

    public function duplicate(Invoice $invoice): RedirectResponse
    {
        $newInvoice = DB::transaction(function () use ($invoice): Invoice {
            $numbering = NumberingSetting::query()->lockForUpdate()->first() ?? NumberingSetting::create(NumberingSetting::defaults());
            [$sequence, $invoiceNo] = $this->nextInvoiceNumber($numbering);
            $invoice = $this->loadInvoice($invoice);

            $copy = Invoice::create([
                'invoice_no' => $invoiceNo,
                'sequence_no' => $sequence,
                'client_id' => $invoice->client_id,
                'project_id' => $invoice->project_id,
                'currency_id' => $invoice->currency_id,
                'tax_setting_id' => $invoice->tax_setting_id,
                'term_condition_id' => $invoice->term_condition_id,
                'invoice_date' => now()->toDateString(),
                'due_date' => $invoice->due_date,
                'subtotal' => $invoice->subtotal,
                'tax_rate_percent' => $invoice->tax_rate_percent,
                'tax_amount' => $invoice->tax_amount,
                'total' => $invoice->total,
                'balance_due' => $invoice->total,
                'status' => Invoice::STATUS_DRAFT,
            ]);

            $copy->items()->createMany($invoice->items->map(fn (InvoiceItem $item) => [
                'item_type' => $item->item_type,
                'item_source_id' => $item->item_source_id,
                'item_name' => $item->item_name,
                'quantity' => $item->quantity,
                'rate' => $item->rate,
                'line_total' => $item->line_total,
            ])->all());

            $numbering->update([
                'next_invoice_number' => max($numbering->next_invoice_number, $sequence + 1),
            ]);

            return $copy;
        });
        ActivityLogger::log('invoices', 'duplicated', auth()->user()->name.' duplicated Invoice '.$invoice->invoice_no.' as '.$newInvoice->invoice_no.'.');

        return redirect()
            ->route('transactions.invoices.edit', $newInvoice)
            ->with('status', 'invoice-duplicated');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        DB::transaction(function () use ($invoice): void {
            $sourceQuotationId = $invoice->source_quotation_id;
            $invoiceNo = $invoice->invoice_no;
            $invoice->delete();
            ActivityLogger::log('invoices', 'archived', auth()->user()->name.' archived Invoice '.$invoiceNo.'.');

            if ($sourceQuotationId) {
                Quotation::query()
                    ->whereKey($sourceQuotationId)
                    ->whereDoesntHave('invoices')
                    ->update([
                        'status' => Quotation::STATUS_APPROVED,
                        'converted_at' => null,
                    ]);
            }
        });

        return redirect()
            ->route('transactions.invoices.index')
            ->with('status', 'invoice-deleted');
    }

    public function pdf(Invoice $invoice): Response
    {
        $pdf = Pdf::loadView('transactions.invoices.pdf', [
            'invoice' => $this->loadInvoice($invoice),
            'company' => CompanySetting::current(),
        ])->setPaper('a4');

        return $pdf->download($invoice->invoice_no.'.pdf');
    }

    public function send(Invoice $invoice): RedirectResponse
    {
        $invoice = $this->loadInvoice($invoice);
        if (! $this->sendInvoiceEmail($invoice, 'invoice')) {
            return redirect()
                ->route('transactions.invoices.index')
                ->with('status', 'invoice-email-not-configured');
        }

        $invoice->update([
            'status' => Invoice::STATUS_SENT,
            'sent_at' => now(),
        ]);
        ActivityLogger::log('invoices', 'sent', auth()->user()->name.' sent Invoice '.$invoice->invoice_no.'.');

        return redirect()
            ->route('transactions.invoices.index')
            ->with('status', 'invoice-sent');
    }

    public function sendReminder(Invoice $invoice): RedirectResponse
    {
        $invoice = $this->loadInvoice($invoice);

        if (! $this->canSendReminder($invoice)) {
            return redirect()
                ->route('transactions.invoices.index')
                ->with('status', 'invoice-reminder-not-ready');
        }

        if (! $this->sendInvoiceEmail($invoice, 'reminder')) {
            return redirect()
                ->route('transactions.invoices.index')
                ->with('status', 'invoice-email-not-configured');
        }
        ActivityLogger::log('invoices', 'reminder_sent', auth()->user()->name.' sent reminder for Invoice '.$invoice->invoice_no.'.');

        return redirect()
            ->route('transactions.invoices.index')
            ->with('status', 'invoice-reminder-sent');
    }

    public function sendOverdue(Invoice $invoice): RedirectResponse
    {
        $invoice = $this->loadInvoice($invoice);
        $this->syncPaymentStatus($invoice);
        $invoice->refresh();

        if (! $this->canSendOverdue($invoice)) {
            return redirect()
                ->route('transactions.invoices.index')
                ->with('status', 'invoice-overdue-not-ready');
        }

        if (! $this->sendInvoiceEmail($invoice, 'overdue')) {
            return redirect()
                ->route('transactions.invoices.index')
                ->with('status', 'invoice-email-not-configured');
        }
        ActivityLogger::log('invoices', 'overdue_sent', auth()->user()->name.' sent overdue email for Invoice '.$invoice->invoice_no.'.');

        return redirect()
            ->route('transactions.invoices.index')
            ->with('status', 'invoice-overdue-sent');
    }

    public function paymentStatus(Invoice $invoice): View
    {
        $this->syncPaymentStatus($invoice);

        return view('transactions.invoices.payment-status', [
            'invoice' => $this->loadInvoice($invoice)->load(['payments' => fn ($query) => $query->latest('payment_date')->latest()]),
            'statuses' => Invoice::statusOptions(),
            'paymentMethods' => InvoicePayment::methodOptions(),
        ]);
    }

    public function storePayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', Rule::in(array_keys(InvoicePayment::methodOptions()))],
            'receipt_number' => ['nullable', 'string', 'max:120'],
            'reference' => ['nullable', 'string', 'max:180'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'receipt_file' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg,webp', 'max:4096'],
        ]);

        DB::transaction(function () use ($request, $invoice, $validated): void {
            $paymentData = [
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'receipt_number' => $validated['receipt_number'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ];

            if ($request->hasFile('receipt_file')) {
                $paymentData = array_merge($paymentData, $this->storeReceipt($request, $invoice));
            }

            $payment = $invoice->payments()->create($paymentData);
            $this->syncPaymentStatus($invoice->refresh());
            ActivityLogger::log('payments', 'created', auth()->user()->name.' added Payment '.$payment->id.' for Invoice '.$invoice->invoice_no.'.');
        });

        return redirect()
            ->route('transactions.invoices.payment-status', $invoice)
            ->with('status', 'payment-added');
    }

    public function destroyPayment(Invoice $invoice, InvoicePayment $payment): RedirectResponse
    {
        abort_if($payment->invoice_id !== $invoice->id, 404);
        $paymentId = $payment->id;
        $invoiceNo = $invoice->invoice_no;

        DB::transaction(function () use ($invoice, $payment, $paymentId, $invoiceNo): void {
            $payment->delete();
            $this->syncPaymentStatus($invoice->refresh());
            ActivityLogger::log('payments', 'archived', auth()->user()->name.' archived Payment '.$paymentId.' from Invoice '.$invoiceNo.'.');
        });

        return redirect()
            ->route('transactions.invoices.payment-status', $invoice)
            ->with('status', 'payment-deleted');
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<int, array<string, mixed>>
     */
    private function buildItems(array $validated): array
    {
        $items = [];

        foreach ($validated['item_type'] as $index => $type) {
            $sourceValue = (string) $validated['item_source_id'][$index];
            $sourceId = is_numeric($sourceValue) ? (int) $sourceValue : null;
            $quantity = (float) $validated['quantity'][$index];
            $rate = (float) $validated['rate'][$index];
            $source = null;

            if ($sourceId) {
                $source = $type === InvoiceItem::TYPE_PRODUCT
                    ? Product::query()->find($sourceId)
                    : Service::query()->find($sourceId);
            }

            $items[] = [
                'item_type' => $type,
                'item_source_id' => $sourceId,
                'item_name' => $source
                    ? ($type === InvoiceItem::TYPE_PRODUCT ? $source->name : $source->long_name)
                    : ($validated['item_name'][$index] ?? 'Quoted Item'),
                'quantity' => $quantity,
                'rate' => $rate,
                'line_total' => round($quantity * $rate, 2),
            ];
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function taxRate(array $validated): float
    {
        if (! empty($validated['tax_setting_id'])) {
            return (float) TaxSetting::query()->whereKey($validated['tax_setting_id'])->value('rate_percent');
        }

        $client = Client::query()->find($validated['client_id']);

        return $client && $client->tax_applicable ? (float) $client->tax_percent : 0.0;
    }

    private function loadInvoice(Invoice $invoice): Invoice
    {
        return $invoice->load(['client', 'project', 'currency', 'taxSetting', 'termCondition', 'items']);
    }

    /**
     * @return array<string, string>
     */
    private function storeReceipt(Request $request, Invoice $invoice): array
    {
        $file = $request->file('receipt_file');
        $filename = 'receipt-'.now()->format('YmdHis').'-'.uniqid().'.'.$file->extension();
        $path = $file->storeAs('receipts/'.$invoice->id, $filename, 'public');

        return [
            'receipt_path' => $path,
            'receipt_web_path' => 'storage/'.$path,
        ];
    }

    private function syncPaymentStatus(Invoice $invoice): void
    {
        if ($invoice->status === Invoice::STATUS_CANCELLED) {
            return;
        }

        $amountPaid = min((float) $invoice->payments()->sum('amount'), (float) $invoice->total);
        $balanceDue = max((float) $invoice->total - $amountPaid, 0);
        $status = $invoice->status;

        if ($balanceDue <= 0 && (float) $invoice->total > 0) {
            $status = Invoice::STATUS_PAID;
        } elseif ($amountPaid > 0) {
            $status = Invoice::STATUS_PARTIALLY_PAID;
        } elseif ($invoice->due_date && $invoice->due_date->isPast()) {
            $status = Invoice::STATUS_OVERDUE;
        } elseif (! in_array($status, [Invoice::STATUS_DRAFT, Invoice::STATUS_SENT], true)) {
            $status = Invoice::STATUS_DRAFT;
        }

        $oldStatus = $invoice->status;

        $invoice->update([
            'amount_paid' => $amountPaid,
            'balance_due' => $balanceDue,
            'status' => $status,
            'paid_at' => $status === Invoice::STATUS_PAID ? ($invoice->paid_at ?? now()) : null,
        ]);

        if ($oldStatus !== $status) {
            ActivityLogger::log('invoices', 'status_changed', 'Invoice '.$invoice->invoice_no.' status changed from '.$oldStatus.' to '.$status.'.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function invoiceFormData(): array
    {
        return [
            'clients' => Client::query()->where('status', Client::STATUS_ACTIVE)->orderBy('name')->get(),
            'projects' => Project::query()->where('status', Project::STATUS_ACTIVE)->orderBy('name')->get(),
            'currencies' => Currency::query()->where('status', Currency::STATUS_ACTIVE)->orderByDesc('is_default')->orderBy('code')->get(),
            'taxes' => TaxSetting::query()->where('status', TaxSetting::STATUS_ACTIVE)->orderByDesc('is_default')->orderBy('name')->get(),
            'terms' => TermCondition::query()->where('status', TermCondition::STATUS_ACTIVE)->orderBy('name')->get(),
            'services' => Service::query()->where('status', Service::STATUS_ACTIVE)->orderBy('long_name')->get(),
            'products' => Product::query()->where('status', Product::STATUS_ACTIVE)->orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedInvoice(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
            'currency_id' => ['required', 'integer', Rule::exists('currencies', 'id')],
            'tax_setting_id' => ['nullable', 'integer', Rule::exists('tax_settings', 'id')],
            'term_condition_id' => ['nullable', 'integer', Rule::exists('terms_conditions', 'id')],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'item_type' => ['required', 'array', 'min:1'],
            'item_type.*' => ['required', Rule::in([InvoiceItem::TYPE_SERVICE, InvoiceItem::TYPE_PRODUCT])],
            'item_source_id' => ['required', 'array', 'min:1'],
            'item_source_id.*' => ['required'],
            'item_name' => ['nullable', 'array'],
            'item_name.*' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'array', 'min:1'],
            'quantity.*' => ['required', 'numeric', 'gt:0'],
            'rate' => ['required', 'array', 'min:1'],
            'rate.*' => ['required', 'numeric', 'min:0'],
            'source_quotation_id' => ['nullable', 'integer', Rule::exists('quotations', 'id')->where('status', Quotation::STATUS_APPROVED)],
        ]);
    }

    private function applyMailSettings(EmailSetting $emailSetting): void
    {
        Config::set('mail.mailers.smtp.host', $emailSetting->mail_host);
        Config::set('mail.mailers.smtp.port', $emailSetting->mail_port);
        Config::set('mail.mailers.smtp.encryption', $emailSetting->mail_encryption);
        Config::set('mail.mailers.smtp.username', $emailSetting->mail_username);
        Config::set('mail.mailers.smtp.password', $emailSetting->mail_password);
        Config::set('mail.from.address', $emailSetting->mail_from_address);
        Config::set('mail.from.name', $emailSetting->mail_from_name);
    }

    private function sendInvoiceEmail(Invoice $invoice, string $template): bool
    {
        $emailSetting = EmailSetting::current();

        if (! $invoice->client?->email || ! $emailSetting->mail_host || ! $emailSetting->mail_from_address) {
            return false;
        }

        $subjectField = $template.'_email_subject';
        $bodyField = $template.'_email_body';
        $this->applyMailSettings($emailSetting);
        $subject = $this->replaceEmailPlaceholders($emailSetting->{$subjectField}, $invoice);
        $body = $this->replaceEmailPlaceholders($emailSetting->{$bodyField}, $invoice);
        $pdfData = Pdf::loadView('transactions.invoices.pdf', [
            'invoice' => $invoice,
            'company' => CompanySetting::current(),
        ])->setPaper('a4')->output();

        Mail::raw($body, function ($message) use ($invoice, $emailSetting, $subject, $pdfData): void {
            $message->to($invoice->client->email)
                ->from($emailSetting->mail_from_address, $emailSetting->mail_from_name)
                ->subject($subject)
                ->attachData($pdfData, $invoice->invoice_no.'.pdf', ['mime' => 'application/pdf']);

            if ($emailSetting->mail_cc_address) {
                $message->cc($emailSetting->mail_cc_address);
            }
        });

        return true;
    }

    private function canSendReminder(Invoice $invoice): bool
    {
        return $invoice->due_date
            && (float) $invoice->balance_due > 0
            && ! in_array($invoice->status, [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED], true)
            && $invoice->due_date->isTomorrow();
    }

    private function canSendOverdue(Invoice $invoice): bool
    {
        return $invoice->due_date
            && (float) $invoice->balance_due > 0
            && ! in_array($invoice->status, [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED], true)
            && now()->startOfDay()->gt($invoice->due_date->copy()->startOfDay());
    }

    private function replaceEmailPlaceholders(string $text, Invoice $invoice): string
    {
        $company = CompanySetting::current();

        return strtr($text, [
            '{client_name}' => $invoice->client?->name ?? '',
            '{invoice_no}' => $invoice->invoice_no,
            '{project_name}' => $invoice->project?->name ?? '',
            '{invoice_date}' => $invoice->invoice_date?->format('d M Y') ?? '',
            '{due_date}' => $invoice->due_date?->format('d M Y') ?? '',
            '{total_amount}' => trim(($invoice->currency?->symbol ?? '').' '.number_format((float) $invoice->total, 2)),
            '{balance_due}' => trim(($invoice->currency?->symbol ?? '').' '.number_format((float) $invoice->balance_due, 2)),
            '{payment_instructions}' => trim(($company->payment_label ?? '')."\n".($company->payment_reference ?? '')."\n".($company->bank_details ?? '')),
            '{company_name}' => $company->company_name ?? config('app.name', 'Hariman Nexus'),
        ]);
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function nextInvoiceNumber(NumberingSetting $numbering): array
    {
        $sequence = $numbering->next_invoice_number;

        do {
            $invoiceNo = $numbering->preview($numbering->invoice_prefix, $sequence, $numbering->include_year_for_invoices);
            $exists = Invoice::query()->where('invoice_no', $invoiceNo)->exists();

            if ($exists) {
                $sequence++;
            }
        } while ($exists);

        return [$sequence, $invoiceNo];
    }
}
