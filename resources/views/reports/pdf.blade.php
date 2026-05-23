<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        body { color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        h2 { border-bottom: 1px solid #dbe3ef; font-size: 14px; margin: 20px 0 8px; padding-bottom: 6px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #f8fafc; color: #475569; font-size: 9px; letter-spacing: .08em; text-align: left; text-transform: uppercase; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 6px; }
        .cards { margin-top: 14px; width: 100%; }
        .cards td { border: 1px solid #e2e8f0; padding: 10px; width: 20%; }
        .label { color: #64748b; font-size: 9px; text-transform: uppercase; }
        .value { font-size: 16px; font-weight: bold; margin-top: 5px; }
        .right { text-align: right; }
    </style>
</head>
<body>
    @php($money = fn ($value) => number_format((float) $value, 2))
    <h1>Hariman Nexus Reports</h1>
    <div>{{ $filters['date_from'] }} to {{ $filters['date_to'] }}</div>

    <table class="cards">
        <tr>
            <td><div class="label">Revenue</div><div class="value">{{ $money($revenue['total_invoiced']) }}</div></td>
            <td><div class="label">Collected</div><div class="value">{{ $money($collections['total_collected']) }}</div></td>
            <td><div class="label">Outstanding</div><div class="value">{{ $money($outstanding['total_outstanding']) }}</div></td>
            <td><div class="label">Overdue</div><div class="value">{{ $money($outstanding['overdue_amount']) }}</div></td>
            <td><div class="label">Conversion</div><div class="value">{{ $conversion['conversion_rate'] }}%</div></td>
        </tr>
    </table>

    <h2>Revenue Report</h2>
    <table>
        <thead><tr><th>Invoice</th><th>Date</th><th>Client</th><th>Status</th><th class="right">Total</th><th class="right">Balance</th></tr></thead>
        <tbody>
            @foreach ($revenue['rows']->take(12) as $invoice)
                <tr><td>{{ $invoice->invoice_no }}</td><td>{{ $invoice->invoice_date?->toDateString() }}</td><td>{{ $invoice->client?->name }}</td><td>{{ $invoice->status }}</td><td class="right">{{ $money($invoice->total) }}</td><td class="right">{{ $money($invoice->balance_due) }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2>Outstanding Invoice Report</h2>
    <table>
        <thead><tr><th>Invoice</th><th>Due Date</th><th>Client</th><th>Status</th><th class="right">Balance</th></tr></thead>
        <tbody>
            @foreach ($outstanding['rows']->take(12) as $invoice)
                <tr><td>{{ $invoice->invoice_no }}</td><td>{{ $invoice->due_date?->toDateString() }}</td><td>{{ $invoice->client?->name }}</td><td>{{ $invoice->status }}</td><td class="right">{{ $money($invoice->balance_due) }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2>Payment Collection Report</h2>
    <table>
        <thead><tr><th>Date</th><th>Invoice</th><th>Client</th><th>Method</th><th class="right">Amount</th></tr></thead>
        <tbody>
            @foreach ($collections['rows']->take(12) as $payment)
                <tr><td>{{ $payment->payment_date?->toDateString() }}</td><td>{{ $payment->invoice?->invoice_no }}</td><td>{{ $payment->invoice?->client?->name }}</td><td>{{ $payment->payment_method }}</td><td class="right">{{ $money($payment->amount) }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2>Quotation Conversion Report</h2>
    <table>
        <thead><tr><th>Quotation</th><th>Date</th><th>Client</th><th>Status</th><th class="right">Total</th></tr></thead>
        <tbody>
            @foreach ($conversion['rows']->take(12) as $quotation)
                <tr><td>{{ $quotation->quotation_no }}</td><td>{{ $quotation->quotation_date?->toDateString() }}</td><td>{{ $quotation->client?->name }}</td><td>{{ $quotation->status }}</td><td class="right">{{ $money($quotation->total) }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
