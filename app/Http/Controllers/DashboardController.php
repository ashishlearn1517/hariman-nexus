<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Project;
use App\Models\Quotation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $dueSoonEnd = now()->addDays(7)->endOfDay();

        $activeInvoices = Invoice::query()->whereNot('status', Invoice::STATUS_CANCELLED);
        $totalRevenue = (float) (clone $activeInvoices)->sum('total');
        $outstanding = (float) (clone $activeInvoices)->where('balance_due', '>', 0)->sum('balance_due');
        $overdueAmount = (float) (clone $activeInvoices)
            ->where('balance_due', '>', 0)
            ->whereDate('due_date', '<', $today)
            ->sum('balance_due');
        $monthlyCollections = (float) InvoicePayment::query()
            ->whereBetween('payment_date', [$monthStart, $monthEnd])
            ->sum('amount');
        $monthlyExpenses = (float) Expense::query()
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->whereNot('status', Expense::STATUS_CANCELLED)
            ->sum('total_amount');

        $quotationsThisMonth = Quotation::query()
            ->whereBetween('quotation_date', [$monthStart, $monthEnd])
            ->count();
        $convertedThisMonth = Quotation::query()
            ->whereBetween('quotation_date', [$monthStart, $monthEnd])
            ->where(fn ($query) => $query
                ->where('status', Quotation::STATUS_CONVERTED)
                ->orWhereHas('invoices'))
            ->count();
        $conversionRate = $quotationsThisMonth > 0
            ? round($convertedThisMonth / $quotationsThisMonth * 100, 2)
            : 0;

        $overdueInvoices = Invoice::query()
            ->with(['client', 'currency'])
            ->whereNot('status', Invoice::STATUS_CANCELLED)
            ->where('balance_due', '>', 0)
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $dueSoonInvoices = Invoice::query()
            ->with(['client', 'currency'])
            ->whereNot('status', Invoice::STATUS_CANCELLED)
            ->where('balance_due', '>', 0)
            ->whereBetween('due_date', [$today, $dueSoonEnd])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $expiringQuotations = Quotation::query()
            ->with(['client', 'currency'])
            ->whereNotIn('status', [Quotation::STATUS_CONVERTED, Quotation::STATUS_REJECTED])
            ->whereBetween('validity_date', [$today, $dueSoonEnd])
            ->orderBy('validity_date')
            ->limit(5)
            ->get();

        $recentInvoices = Invoice::query()
            ->with(['client', 'currency'])
            ->latest('invoice_date')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', [
            'financial' => [
                'totalRevenue' => $totalRevenue,
                'outstanding' => $outstanding,
                'overdue' => $overdueAmount,
                'monthlyCollections' => $monthlyCollections,
                'monthlyExpenses' => $monthlyExpenses,
                'netFlow' => $monthlyCollections - $monthlyExpenses,
                'currencyBreakdown' => $this->currencyBreakdown(),
            ],
            'operations' => [
                'quotationsThisMonth' => $quotationsThisMonth,
                'conversionRate' => $conversionRate,
                'activeClients' => Client::query()->where('status', Client::STATUS_ACTIVE)->count(),
                'activeProjects' => Project::query()->where('status', Project::STATUS_ACTIVE)->count(),
            ],
            'alerts' => [
                'overdueInvoices' => $overdueInvoices,
                'dueSoonInvoices' => $dueSoonInvoices,
                'expiringQuotations' => $expiringQuotations,
            ],
            'recentInvoices' => $recentInvoices,
            'charts' => [
                'cashMonths' => $this->lastSixMonths()->map(fn (Carbon $month) => $month->format('M Y'))->values(),
                'revenue' => $this->monthlyInvoiceTotals(),
                'collections' => $this->monthlyCollectionTotals(),
                'expenses' => $this->monthlyExpenseTotals(),
                'quotationPipeline' => [
                    'Draft' => Quotation::query()->where('status', Quotation::STATUS_DRAFT)->count(),
                    'Sent' => Quotation::query()->where('status', Quotation::STATUS_SENT)->count(),
                    'Approved' => Quotation::query()->where('status', Quotation::STATUS_APPROVED)->count(),
                    'Converted' => Quotation::query()->where('status', Quotation::STATUS_CONVERTED)->count(),
                ],
            ],
        ]);
    }

    private function currencyBreakdown(): Collection
    {
        return Invoice::query()
            ->with('currency')
            ->whereNot('status', Invoice::STATUS_CANCELLED)
            ->get()
            ->groupBy(fn (Invoice $invoice) => $invoice->currency?->code ?: 'N/A')
            ->map(fn (Collection $invoices) => [
                'total' => (float) $invoices->sum('total'),
                'outstanding' => (float) $invoices->sum('balance_due'),
            ])
            ->sortKeys();
    }

    private function lastSixMonths(): Collection
    {
        return collect(range(5, 0))->map(fn (int $monthsAgo) => now()->startOfMonth()->subMonths($monthsAgo));
    }

    private function monthlyInvoiceTotals(): Collection
    {
        return $this->lastSixMonths()->map(fn (Carbon $month) => (float) Invoice::query()
            ->whereNot('status', Invoice::STATUS_CANCELLED)
            ->whereBetween('invoice_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->sum('total'));
    }

    private function monthlyCollectionTotals(): Collection
    {
        return $this->lastSixMonths()->map(fn (Carbon $month) => (float) InvoicePayment::query()
            ->whereBetween('payment_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->sum('amount'));
    }

    private function monthlyExpenseTotals(): Collection
    {
        return $this->lastSixMonths()->map(fn (Carbon $month) => (float) Expense::query()
            ->whereNot('status', Expense::STATUS_CANCELLED)
            ->whereBetween('expense_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->sum('total_amount'));
    }
}
