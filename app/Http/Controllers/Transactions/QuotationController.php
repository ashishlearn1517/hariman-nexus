<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Currency;
use App\Models\EmailSetting;
use App\Models\NumberingSetting;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\QuotationItem;
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
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class QuotationController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = (string) $request->query('status', '');

        return view('transactions.quotations.index', array_merge($this->quotationFormData(), [
            'quotations' => Quotation::query()
                ->with(['client', 'project', 'currency'])
                ->withCount('invoices')
                ->when($statusFilter !== '', fn ($query) => $query->where('status', $statusFilter))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'statuses' => Quotation::statusOptions(),
            'statusFilter' => $statusFilter,
        ]));
    }

    public function show(Quotation $quotation): View
    {
        return view('transactions.quotations.show', [
            'quotation' => $this->loadQuotation($quotation),
            'company' => CompanySetting::current(),
            'statuses' => Quotation::statusOptions(),
        ]);
    }

    public function status(Quotation $quotation): View
    {
        return view('transactions.quotations.status', [
            'quotation' => $this->loadQuotation($quotation),
            'statuses' => Quotation::statusOptions(),
        ]);
    }

    public function updateStatus(Request $request, Quotation $quotation): RedirectResponse
    {
        if ($quotation->status === Quotation::STATUS_APPROVED) {
            return redirect()
                ->route('transactions.quotations.status', $quotation)
                ->with('status', 'quotation-locked');
        }

        $validated = $request->validate([
            'rate' => ['required', 'array', 'min:1'],
            'rate.*' => ['required', 'numeric', 'min:0'],
            'action' => ['required', Rule::in(['save', 'approve', 'reject'])],
        ]);

        $oldStatus = $quotation->status;

        DB::transaction(function () use ($quotation, $validated): void {
            foreach ($validated['rate'] as $itemId => $rate) {
                $item = $quotation->items()->whereKey($itemId)->firstOrFail();

                $item->update([
                    'rate' => $rate,
                    'line_total' => round((float) $item->quantity * (float) $rate, 2),
                ]);
            }

            $this->recalculateTotals($quotation->refresh());

            if ($validated['action'] === 'approve') {
                $quotation->update([
                    'status' => Quotation::STATUS_APPROVED,
                    'approved_at' => now(),
                    'rejected_at' => null,
                ]);
            }

            if ($validated['action'] === 'reject') {
                $quotation->update([
                    'status' => Quotation::STATUS_REJECTED,
                    'rejected_at' => now(),
                ]);
            }
        });

        $quotation->refresh();
        $actionDescription = match ($validated['action']) {
            'approve' => 'approved',
            'reject' => 'rejected',
            default => 'updated rates for',
        };
        ActivityLogger::log('quotations', $validated['action'] === 'save' ? 'rates_updated' : $validated['action'], auth()->user()->name.' '.$actionDescription.' Quotation '.$quotation->quotation_no.'.');

        if ($oldStatus !== $quotation->status) {
            ActivityLogger::log('quotations', 'status_changed', 'Quotation '.$quotation->quotation_no.' status changed from '.$oldStatus.' to '.$quotation->status.'.');
        }

        return redirect()
            ->route('transactions.quotations.status', $quotation)
            ->with('status', 'quotation-status-updated');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedQuotation($request);

        DB::transaction(function () use ($validated): void {
            $items = $this->buildItems($validated);
            $subtotal = collect($items)->sum('line_total');
            $taxRate = $this->taxRate($validated);
            $taxAmount = round($subtotal * $taxRate / 100, 2);
            $numbering = NumberingSetting::query()->lockForUpdate()->first() ?? NumberingSetting::create(NumberingSetting::defaults());
            [$sequence, $quotationNo] = $this->nextQuotationNumber($numbering);

            $quotation = Quotation::create([
                'quotation_no' => $quotationNo,
                'sequence_no' => $sequence,
                'client_id' => $validated['client_id'],
                'project_id' => $validated['project_id'],
                'currency_id' => $validated['currency_id'],
                'tax_setting_id' => $validated['tax_setting_id'] ?? null,
                'term_condition_id' => $validated['term_condition_id'] ?? null,
                'quotation_date' => $validated['quotation_date'],
                'validity_date' => $validated['validity_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_rate_percent' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'status' => Quotation::STATUS_DRAFT,
            ]);

            $quotation->items()->createMany($items);
            ActivityLogger::log('quotations', 'created', auth()->user()->name.' created Quotation '.$quotation->quotation_no.'.');

            $numbering->update([
                'next_quotation_number' => max($numbering->next_quotation_number, $sequence + 1),
            ]);
        });

        return redirect()
            ->route('transactions.quotations.index')
            ->with('status', 'quotation-created');
    }

    public function edit(Quotation $quotation): View
    {
        abort_if($quotation->status === Quotation::STATUS_APPROVED, 403);

        return view('transactions.quotations.edit', array_merge($this->quotationFormData(), [
            'quotation' => $this->loadQuotation($quotation),
        ]));
    }

    public function update(Request $request, Quotation $quotation): RedirectResponse
    {
        abort_if($quotation->status === Quotation::STATUS_APPROVED, 403);

        $validated = $this->validatedQuotation($request);
        $quotationNo = $quotation->quotation_no;

        DB::transaction(function () use ($quotation, $validated): void {
            $items = $this->buildItems($validated);
            $subtotal = collect($items)->sum('line_total');
            $taxRate = $this->taxRate($validated);
            $taxAmount = round($subtotal * $taxRate / 100, 2);

            $quotation->update([
                'client_id' => $validated['client_id'],
                'project_id' => $validated['project_id'],
                'currency_id' => $validated['currency_id'],
                'tax_setting_id' => $validated['tax_setting_id'] ?? null,
                'term_condition_id' => $validated['term_condition_id'] ?? null,
                'quotation_date' => $validated['quotation_date'],
                'validity_date' => $validated['validity_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_rate_percent' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
            ]);

            $quotation->items()->delete();
            $quotation->items()->createMany($items);
        });
        ActivityLogger::log('quotations', 'updated', $request->user()->name.' edited Quotation '.$quotationNo.'.');

        return redirect()
            ->route('transactions.quotations.index')
            ->with('status', 'quotation-updated');
    }

    public function duplicate(Quotation $quotation): RedirectResponse
    {
        $newQuotation = DB::transaction(function () use ($quotation): Quotation {
            $numbering = NumberingSetting::query()->lockForUpdate()->first() ?? NumberingSetting::create(NumberingSetting::defaults());
            [$sequence, $quotationNo] = $this->nextQuotationNumber($numbering);
            $quotation = $this->loadQuotation($quotation);

            $copy = Quotation::create([
                'quotation_no' => $quotationNo,
                'sequence_no' => $sequence,
                'client_id' => $quotation->client_id,
                'project_id' => $quotation->project_id,
                'currency_id' => $quotation->currency_id,
                'tax_setting_id' => $quotation->tax_setting_id,
                'term_condition_id' => $quotation->term_condition_id,
                'quotation_date' => now()->toDateString(),
                'validity_date' => $quotation->validity_date,
                'subtotal' => $quotation->subtotal,
                'tax_rate_percent' => $quotation->tax_rate_percent,
                'tax_amount' => $quotation->tax_amount,
                'total' => $quotation->total,
                'status' => Quotation::STATUS_DRAFT,
            ]);

            $copy->items()->createMany($quotation->items->map(fn (QuotationItem $item) => [
                'item_type' => $item->item_type,
                'item_source_id' => $item->item_source_id,
                'item_name' => $item->item_name,
                'quantity' => $item->quantity,
                'rate' => $item->rate,
                'line_total' => $item->line_total,
            ])->all());

            $numbering->update([
                'next_quotation_number' => max($numbering->next_quotation_number, $sequence + 1),
            ]);

            return $copy;
        });
        ActivityLogger::log('quotations', 'duplicated', auth()->user()->name.' duplicated Quotation '.$quotation->quotation_no.' as '.$newQuotation->quotation_no.'.');

        return redirect()
            ->route('transactions.quotations.edit', $newQuotation)
            ->with('status', 'quotation-duplicated');
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        if ($quotation->status === Quotation::STATUS_CONVERTED && $quotation->invoices()->exists()) {
            return redirect()
                ->route('transactions.quotations.index')
                ->with('status', 'quotation-delete-blocked-converted');
        }

        DB::transaction(function () use ($quotation): void {
            $quotationNo = $quotation->quotation_no;
            $quotation->delete();
            ActivityLogger::log('quotations', 'archived', auth()->user()->name.' archived Quotation '.$quotationNo.'.');
        });

        return redirect()
            ->route('transactions.quotations.index')
            ->with('status', 'quotation-deleted');
    }

    public function pdf(Quotation $quotation): Response
    {
        $quotation = $this->loadQuotation($quotation);
        $pdf = Pdf::loadView('transactions.quotations.pdf', [
            'quotation' => $quotation,
            'company' => CompanySetting::current(),
        ])->setPaper('a4');

        return $pdf->download($quotation->quotation_no.'.pdf');
    }

    public function send(Quotation $quotation): RedirectResponse
    {
        $quotation = $this->loadQuotation($quotation);
        $emailSetting = EmailSetting::current();

        if (! $quotation->client?->email || ! $emailSetting->mail_host || ! $emailSetting->mail_from_address) {
            return redirect()
                ->route('transactions.quotations.index')
                ->with('status', 'quotation-email-not-configured');
        }

        $this->applyMailSettings($emailSetting);
        $subject = $this->replaceEmailPlaceholders($emailSetting->quotation_email_subject, $quotation);
        $body = $this->replaceEmailPlaceholders($emailSetting->quotation_email_body, $quotation);
        $pdfData = Pdf::loadView('transactions.quotations.pdf', [
            'quotation' => $quotation,
            'company' => CompanySetting::current(),
        ])->setPaper('a4')->output();

        Mail::raw($body, function ($message) use ($quotation, $emailSetting, $subject, $pdfData): void {
            $message->to($quotation->client->email)
                ->from($emailSetting->mail_from_address, $emailSetting->mail_from_name)
                ->subject($subject)
                ->attachData($pdfData, $quotation->quotation_no.'.pdf', ['mime' => 'application/pdf']);

            if ($emailSetting->mail_cc_address) {
                $message->cc($emailSetting->mail_cc_address);
            }
        });

        $quotation->update([
            'status' => Quotation::STATUS_SENT,
            'sent_at' => now(),
        ]);
        ActivityLogger::log('quotations', 'sent', auth()->user()->name.' sent Quotation '.$quotation->quotation_no.'.');

        return redirect()
            ->route('transactions.quotations.index')
            ->with('status', 'quotation-sent');
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<int, array<string, mixed>>
     */
    private function buildItems(array $validated): array
    {
        $items = [];

        foreach ($validated['item_type'] as $index => $type) {
            $sourceId = (int) $validated['item_source_id'][$index];
            $quantity = (float) $validated['quantity'][$index];
            $rate = (float) $validated['rate'][$index];

            $source = $type === QuotationItem::TYPE_PRODUCT
                ? Product::query()->findOrFail($sourceId)
                : Service::query()->findOrFail($sourceId);

            $items[] = [
                'item_type' => $type,
                'item_source_id' => $sourceId,
                'item_name' => $type === QuotationItem::TYPE_PRODUCT ? $source->name : $source->long_name,
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

    private function recalculateTotals(Quotation $quotation): void
    {
        $subtotal = (float) $quotation->items()->sum('line_total');
        $taxAmount = round($subtotal * (float) $quotation->tax_rate_percent / 100, 2);

        $quotation->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $subtotal + $taxAmount,
        ]);
    }

    private function loadQuotation(Quotation $quotation): Quotation
    {
        return $quotation->load([
            'client',
            'project',
            'currency',
            'taxSetting',
            'termCondition',
            'items',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function quotationFormData(): array
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
    private function validatedQuotation(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
            'currency_id' => ['required', 'integer', Rule::exists('currencies', 'id')],
            'tax_setting_id' => ['nullable', 'integer', Rule::exists('tax_settings', 'id')],
            'term_condition_id' => ['nullable', 'integer', Rule::exists('terms_conditions', 'id')],
            'quotation_date' => ['required', 'date'],
            'validity_date' => ['nullable', 'date', 'after_or_equal:quotation_date'],
            'item_type' => ['required', 'array', 'min:1'],
            'item_type.*' => ['required', Rule::in([QuotationItem::TYPE_SERVICE, QuotationItem::TYPE_PRODUCT])],
            'item_source_id' => ['required', 'array', 'min:1'],
            'item_source_id.*' => ['required', 'integer'],
            'quantity' => ['required', 'array', 'min:1'],
            'quantity.*' => ['required', 'numeric', 'gt:0'],
            'rate' => ['required', 'array', 'min:1'],
            'rate.*' => ['required', 'numeric', 'min:0'],
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

    private function replaceEmailPlaceholders(string $text, Quotation $quotation): string
    {
        return strtr($text, [
            '{client_name}' => $quotation->client?->name ?? '',
            '{quotation_no}' => $quotation->quotation_no,
            '{project_name}' => $quotation->project?->name ?? '',
            '{quotation_date}' => $quotation->quotation_date?->format('d M Y') ?? '',
            '{validity_date}' => $quotation->validity_date?->format('d M Y') ?? '',
            '{total_amount}' => trim(($quotation->currency?->symbol ?? '').' '.number_format((float) $quotation->total, 2)),
            '{company_name}' => CompanySetting::current()->company_name ?? config('app.name', 'Hariman Nexus'),
        ]);
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function nextQuotationNumber(NumberingSetting $numbering): array
    {
        $sequence = $numbering->next_quotation_number;

        do {
            $quotationNo = $numbering->preview(
                $numbering->quotation_prefix,
                $sequence,
                $numbering->include_year_for_quotations,
            );
            $exists = Quotation::query()->where('quotation_no', $quotationNo)->exists();

            if ($exists) {
                $sequence++;
            }
        } while ($exists);

        return [$sequence, $quotationNo];
    }
}
