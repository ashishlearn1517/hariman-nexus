<?php

namespace App\Http\Controllers;

use App\Exports\ReportRowsExport;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Project;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View|StreamedResponse|BinaryFileResponse
    {
        $filters = $this->filters($request);

        $data = [
            'filters' => $filters,
            'clients' => Client::query()->orderBy('name')->get(),
            'revenue' => $this->revenueReport($filters),
            'outstanding' => $this->outstandingReport($filters),
            'clientStatement' => $this->clientStatement($filters),
            'collections' => $this->collectionReport($filters),
            'conversion' => $this->conversionReport($filters),
            'expenses' => $this->expenseReport($filters),
            'profitLoss' => $this->profitLossReport($filters),
            'projectProfitability' => $this->projectProfitabilityReport($filters),
        ];

        if ($request->query('export')) {
            return $this->export((string) $request->query('export'), $data);
        }

        return view('reports.index', $data);
    }

    /**
     * @return array{date_from: string, date_to: string, client_id: string, currency: string}
     */
    private function filters(Request $request): array
    {
        return [
            'date_from' => $request->query('date_from', now()->startOfYear()->toDateString()),
            'date_to' => $request->query('date_to', now()->toDateString()),
            'client_id' => (string) $request->query('client_id', ''),
            'currency' => (string) $request->query('currency', ''),
        ];
    }

    /**
     * @param array<string, string> $filters
     */
    private function invoiceScope(array $filters): Builder
    {
        return Invoice::query()
            ->with(['client', 'currency'])
            ->whereBetween('invoice_date', [$filters['date_from'], $filters['date_to']])
            ->when($filters['client_id'] !== '', fn ($query) => $query->where('client_id', $filters['client_id']))
            ->when($filters['currency'] !== '', fn ($query) => $query->whereHas('currency', fn ($currency) => $currency->where('code', $filters['currency'])));
    }

    /**
     * @param array<string, string> $filters
     */
    private function paymentScope(array $filters): Builder
    {
        return InvoicePayment::query()
            ->with(['invoice.client', 'invoice.currency'])
            ->whereBetween('payment_date', [$filters['date_from'], $filters['date_to']])
            ->when($filters['client_id'] !== '', fn ($query) => $query->whereHas('invoice', fn ($invoice) => $invoice->where('client_id', $filters['client_id'])))
            ->when($filters['currency'] !== '', fn ($query) => $query->whereHas('invoice.currency', fn ($currency) => $currency->where('code', $filters['currency'])));
    }

    /**
     * @param array<string, string> $filters
     * @return array<string, mixed>
     */
    private function revenueReport(array $filters): array
    {
        $invoices = $this->invoiceScope($filters)->get();
        $payments = $this->paymentScope($filters)->get();

        return [
            'total_invoiced' => (float) $invoices->sum('total'),
            'total_collected' => (float) $payments->sum('amount'),
            'invoice_count' => $invoices->count(),
            'average_invoice' => $invoices->count() ? round((float) $invoices->avg('total'), 2) : 0,
            'monthly' => $invoices
                ->groupBy(fn (Invoice $invoice) => $invoice->invoice_date?->format('M Y'))
                ->map(fn (Collection $rows) => round((float) $rows->sum('total'), 2)),
            'by_status' => $invoices
                ->groupBy('status')
                ->map(fn (Collection $rows) => round((float) $rows->sum('total'), 2)),
            'rows' => $invoices->sortByDesc('invoice_date')->values(),
        ];
    }

    /**
     * @param array<string, string> $filters
     * @return array<string, mixed>
     */
    private function outstandingReport(array $filters): array
    {
        $invoices = $this->invoiceScope($filters)
            ->where('balance_due', '>', 0)
            ->whereNot('status', Invoice::STATUS_CANCELLED)
            ->get();

        return [
            'total_outstanding' => (float) $invoices->sum('balance_due'),
            'overdue_amount' => (float) $invoices->filter(fn (Invoice $invoice) => $invoice->due_date && $invoice->due_date->isPast())->sum('balance_due'),
            'open_count' => $invoices->count(),
            'overdue_count' => $invoices->filter(fn (Invoice $invoice) => $invoice->due_date && $invoice->due_date->isPast())->count(),
            'aging' => [
                'Current' => (float) $invoices->filter(fn (Invoice $invoice) => ! $invoice->due_date || ! $invoice->due_date->isPast())->sum('balance_due'),
                '1-30 Days' => (float) $invoices->filter(fn (Invoice $invoice) => $this->daysOverdue($invoice) >= 1 && $this->daysOverdue($invoice) <= 30)->sum('balance_due'),
                '31-60 Days' => (float) $invoices->filter(fn (Invoice $invoice) => $this->daysOverdue($invoice) >= 31 && $this->daysOverdue($invoice) <= 60)->sum('balance_due'),
                '60+ Days' => (float) $invoices->filter(fn (Invoice $invoice) => $this->daysOverdue($invoice) > 60)->sum('balance_due'),
            ],
            'rows' => $invoices->sortBy('due_date')->values(),
        ];
    }

    /**
     * @param array<string, string> $filters
     * @return array<string, mixed>
     */
    private function clientStatement(array $filters): array
    {
        $clientId = $filters['client_id'] !== '' ? (int) $filters['client_id'] : null;
        $client = $clientId ? Client::query()->find($clientId) : null;

        $invoices = $this->invoiceScope($filters)->when($clientId, fn ($query) => $query->where('client_id', $clientId))->get();
        $payments = $this->paymentScope($filters)->when($clientId, fn ($query) => $query->whereHas('invoice', fn ($invoice) => $invoice->where('client_id', $clientId)))->get();

        $entries = collect()
            ->merge($invoices->map(fn (Invoice $invoice) => [
                'date' => $invoice->invoice_date,
                'type' => 'Invoice',
                'reference' => $invoice->invoice_no,
                'client' => $invoice->client?->name,
                'debit' => (float) $invoice->total,
                'credit' => 0,
            ]))
            ->merge($payments->map(fn (InvoicePayment $payment) => [
                'date' => $payment->payment_date,
                'type' => 'Payment',
                'reference' => $payment->receipt_number ?: 'PAY-'.$payment->id,
                'client' => $payment->invoice?->client?->name,
                'debit' => 0,
                'credit' => (float) $payment->amount,
            ]))
            ->sortBy('date')
            ->values();

        $balance = 0;
        $entries = $entries->map(function (array $entry) use (&$balance) {
            $balance += $entry['debit'] - $entry['credit'];
            $entry['balance'] = $balance;

            return $entry;
        });

        return [
            'client' => $client,
            'total_debit' => (float) $entries->sum('debit'),
            'total_credit' => (float) $entries->sum('credit'),
            'closing_balance' => $balance,
            'rows' => $entries,
            'by_client' => $this->invoiceScope($filters)
                ->select('client_id', DB::raw('SUM(balance_due) as balance_due'))
                ->with('client')
                ->groupBy('client_id')
                ->orderByDesc('balance_due')
                ->get(),
        ];
    }

    /**
     * @param array<string, string> $filters
     * @return array<string, mixed>
     */
    private function collectionReport(array $filters): array
    {
        $payments = $this->paymentScope($filters)->get();

        return [
            'total_collected' => (float) $payments->sum('amount'),
            'payment_count' => $payments->count(),
            'average_payment' => $payments->count() ? round((float) $payments->avg('amount'), 2) : 0,
            'by_method' => $payments
                ->groupBy('payment_method')
                ->map(fn (Collection $rows) => round((float) $rows->sum('amount'), 2)),
            'monthly' => $payments
                ->groupBy(fn (InvoicePayment $payment) => $payment->payment_date?->format('M Y'))
                ->map(fn (Collection $rows) => round((float) $rows->sum('amount'), 2)),
            'rows' => $payments->sortByDesc('payment_date')->values(),
        ];
    }

    /**
     * @param array<string, string> $filters
     * @return array<string, mixed>
     */
    private function conversionReport(array $filters): array
    {
        $quotations = Quotation::query()
            ->with(['client', 'currency', 'invoices'])
            ->whereBetween('quotation_date', [$filters['date_from'], $filters['date_to']])
            ->when($filters['client_id'] !== '', fn ($query) => $query->where('client_id', $filters['client_id']))
            ->when($filters['currency'] !== '', fn ($query) => $query->whereHas('currency', fn ($currency) => $currency->where('code', $filters['currency'])))
            ->get();

        $converted = $quotations->filter(fn (Quotation $quotation) => $quotation->status === Quotation::STATUS_CONVERTED || $quotation->invoices->isNotEmpty());
        $approved = $quotations->where('status', Quotation::STATUS_APPROVED);

        return [
            'quotation_count' => $quotations->count(),
            'converted_count' => $converted->count(),
            'approved_count' => $approved->count(),
            'conversion_rate' => $quotations->count() ? round($converted->count() / $quotations->count() * 100, 2) : 0,
            'quoted_value' => (float) $quotations->sum('total'),
            'converted_value' => (float) $converted->sum('total'),
            'by_status' => $quotations
                ->groupBy('status')
                ->map(fn (Collection $rows) => $rows->count()),
            'monthly' => $quotations
                ->groupBy(fn (Quotation $quotation) => $quotation->quotation_date?->format('M Y'))
                ->map(fn (Collection $rows) => [
                    'quoted' => $rows->count(),
                    'converted' => $rows->filter(fn (Quotation $quotation) => $quotation->status === Quotation::STATUS_CONVERTED || $quotation->invoices->isNotEmpty())->count(),
                ]),
            'rows' => $quotations->sortByDesc('quotation_date')->values(),
        ];
    }

    /**
     * @param array<string, string> $filters
     * @return array<string, mixed>
     */
    private function expenseReport(array $filters): array
    {
        $expenses = Expense::query()
            ->with(['category', 'project'])
            ->whereBetween('expense_date', [$filters['date_from'], $filters['date_to']])
            ->whereNot('status', Expense::STATUS_CANCELLED)
            ->get();

        return [
            'total' => (float) $expenses->sum('total_amount'),
            'count' => $expenses->count(),
            'average' => $expenses->count() ? round((float) $expenses->avg('total_amount'), 2) : 0,
            'monthly' => $expenses
                ->groupBy(fn (Expense $expense) => $expense->expense_date?->format('M Y'))
                ->map(fn (Collection $rows) => round((float) $rows->sum('total_amount'), 2)),
            'by_category' => $expenses
                ->groupBy(fn (Expense $expense) => $expense->category?->category_name ?? 'Uncategorized')
                ->map(fn (Collection $rows) => round((float) $rows->sum('total_amount'), 2))
                ->sortDesc(),
            'by_vendor' => $expenses
                ->groupBy(fn (Expense $expense) => $expense->vendor?->vendor_name ?? $expense->vendor_name ?? 'No Vendor')
                ->map(fn (Collection $rows) => round((float) $rows->sum('total_amount'), 2))
                ->sortDesc(),
            'rows' => $expenses->sortByDesc('expense_date')->values(),
        ];
    }

    /**
     * @param array<string, string> $filters
     * @return array<string, mixed>
     */
    private function profitLossReport(array $filters): array
    {
        $revenue = (float) $this->paymentScope($filters)->sum('amount');
        $expenses = (float) Expense::query()
            ->whereBetween('expense_date', [$filters['date_from'], $filters['date_to']])
            ->whereNot('status', Expense::STATUS_CANCELLED)
            ->sum('total_amount');

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'profit' => $revenue - $expenses,
            'rows' => collect([
                ['type' => 'Revenue', 'amount' => $revenue],
                ['type' => 'Expenses', 'amount' => $expenses],
                ['type' => 'Profit', 'amount' => $revenue - $expenses],
            ]),
        ];
    }

    /**
     * @param array<string, string> $filters
     * @return array<string, mixed>
     */
    private function projectProfitabilityReport(array $filters): array
    {
        $projects = Project::query()
            ->with(['invoices' => fn ($query) => $query
                ->whereBetween('invoice_date', [$filters['date_from'], $filters['date_to']])
                ->whereNot('status', Invoice::STATUS_CANCELLED),
                'expenses' => fn ($query) => $query
                    ->whereBetween('expense_date', [$filters['date_from'], $filters['date_to']])
                    ->whereNot('status', Expense::STATUS_CANCELLED),
            ])
            ->orderBy('name')
            ->get();

        $rows = $projects->map(function (Project $project): array {
            $revenue = (float) $project->invoices->sum('total');
            $expenses = (float) $project->expenses->sum('total_amount');

            return [
                'project' => $project->name,
                'revenue' => $revenue,
                'expenses' => $expenses,
                'profit' => $revenue - $expenses,
            ];
        })->filter(fn (array $row) => $row['revenue'] > 0 || $row['expenses'] > 0)->values();

        return [
            'rows' => $rows,
            'total_revenue' => (float) $rows->sum('revenue'),
            'total_expenses' => (float) $rows->sum('expenses'),
            'total_profit' => (float) $rows->sum('profit'),
        ];
    }

    private function daysOverdue(Invoice $invoice): int
    {
        if (! $invoice->due_date || ! $invoice->due_date->isPast()) {
            return 0;
        }

        return $invoice->due_date->startOfDay()->diffInDays(now()->startOfDay());
    }

    /**
     * @param array<string, mixed> $data
     */
    private function export(string $type, array $data): StreamedResponse|BinaryFileResponse
    {
        if ($type === 'pdf') {
            return response()->streamDownload(function () use ($data): void {
                echo Pdf::loadView('reports.pdf', $data)->setPaper('a4', 'landscape')->output();
            }, 'hariman-nexus-reports.pdf', ['Content-Type' => 'application/pdf']);
        }

        $isExcel = str_ends_with($type, '_xlsx');
        $reportType = $isExcel ? str_replace('_xlsx', '', $type) : $type;
        [$headers, $rows] = $this->exportRows($reportType, $data);

        if ($isExcel) {
            return Excel::download(new ReportRowsExport($headers, $rows->values()->all()), $reportType.'-report.xlsx');
        }

        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $type.'-report.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{0: array<int, string>, 1: \Illuminate\Support\Collection<int, array<int, mixed>>}
     */
    private function exportRows(string $type, array $data): array
    {
        $rows = match ($type) {
            'revenue' => $data['revenue']['rows']->map(fn (Invoice $invoice) => [
                $invoice->invoice_no,
                $invoice->invoice_date?->toDateString(),
                $invoice->client?->name,
                $invoice->currency?->code,
                $invoice->status,
                $invoice->total,
                $invoice->amount_paid,
                $invoice->balance_due,
            ]),
            'outstanding' => $data['outstanding']['rows']->map(fn (Invoice $invoice) => [
                $invoice->invoice_no,
                $invoice->due_date?->toDateString(),
                $invoice->client?->name,
                $invoice->currency?->code,
                $invoice->status,
                $invoice->balance_due,
            ]),
            'client_statement' => $data['clientStatement']['rows']->map(fn (array $entry) => [
                $entry['date']?->toDateString(),
                $entry['type'],
                $entry['reference'],
                $entry['client'],
                $entry['debit'],
                $entry['credit'],
                $entry['balance'],
            ]),
            'collections' => $data['collections']['rows']->map(fn (InvoicePayment $payment) => [
                $payment->payment_date?->toDateString(),
                $payment->invoice?->invoice_no,
                $payment->invoice?->client?->name,
                $payment->payment_method,
                $payment->amount,
                $payment->reference,
            ]),
            'conversion' => $data['conversion']['rows']->map(fn (Quotation $quotation) => [
                $quotation->quotation_no,
                $quotation->quotation_date?->toDateString(),
                $quotation->client?->name,
                $quotation->currency?->code,
                $quotation->status,
                $quotation->total,
                $quotation->invoices->pluck('invoice_no')->join('|'),
            ]),
            'expenses' => $data['expenses']['rows']->map(fn (Expense $expense) => [
                $expense->expense_no,
                $expense->expense_date?->toDateString(),
                $expense->category?->category_name,
                $expense->project?->name,
                $expense->vendor_name,
                $expense->payment_method,
                $expense->status,
                $expense->amount,
                $expense->tax_amount,
                $expense->total_amount,
            ]),
            'profit_loss' => $data['profitLoss']['rows']->map(fn (array $row) => [
                $row['type'],
                $row['amount'],
            ]),
            'project_profitability' => $data['projectProfitability']['rows']->map(fn (array $row) => [
                $row['project'],
                $row['revenue'],
                $row['expenses'],
                $row['profit'],
            ]),
            default => collect(),
        };

        $headers = match ($type) {
            'revenue' => ['Invoice', 'Date', 'Client', 'Currency', 'Status', 'Total', 'Paid', 'Balance'],
            'outstanding' => ['Invoice', 'Due Date', 'Client', 'Currency', 'Status', 'Balance Due'],
            'client_statement' => ['Date', 'Type', 'Reference', 'Client', 'Debit', 'Credit', 'Balance'],
            'collections' => ['Date', 'Invoice', 'Client', 'Method', 'Amount', 'Reference'],
            'conversion' => ['Quotation', 'Date', 'Client', 'Currency', 'Status', 'Total', 'Invoices'],
            'expenses' => ['Expense', 'Date', 'Category', 'Project', 'Vendor', 'Payment Method', 'Status', 'Amount', 'Tax', 'Total'],
            'profit_loss' => ['Type', 'Amount'],
            'project_profitability' => ['Project', 'Revenue', 'Expenses', 'Profit'],
            default => ['No data'],
        };

        return [$headers, $rows];
    }
}
