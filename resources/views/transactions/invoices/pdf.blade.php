@php
    $logoPath = $company->company_logo_web_path ?: 'assets/images/hariman-nexus-wordmark.png';
    $logoFile = public_path($logoPath);
    $logoInfo = pathinfo($logoFile);
    $jpegFallback = ($logoInfo['dirname'] ?? '').DIRECTORY_SEPARATOR.($logoInfo['filename'] ?? 'logo').'-pdf.jpg';
    $pdfLogoFile = is_file($jpegFallback) ? $jpegFallback : $logoFile;
    $canRenderLogo = is_file($pdfLogoFile) && (extension_loaded('gd') || in_array(strtolower(pathinfo($pdfLogoFile, PATHINFO_EXTENSION)), ['jpg', 'jpeg'], true));
    $currencySymbol = $invoice->currency?->symbol ?: $invoice->currency?->code;
    $companyContact = $company->phone();
    $companyAddress = trim($company->company_address ?: collect([$company->company_location, $company->company_location_country])->filter()->implode(', '));
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4; margin: 8mm; }
        body { color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.45; margin: 0; }
        table { border-collapse: collapse; width: 100%; }
        .head td { border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; vertical-align: top; }
        .logo { max-height: 90px; max-width: 300px; }
        .title { font-size: 28px; font-weight: 700; letter-spacing: .04em; text-align: right; text-transform: uppercase; }
        .meta { color: #475569; line-height: 1.8; margin-top: 8px; text-align: right; }
        .label { color: #475569; font-size: 10px; font-weight: 700; letter-spacing: .14em; margin-bottom: 8px; text-transform: uppercase; }
        .bill td { border-bottom: 1px solid #e2e8f0; padding: 14px 0 16px; vertical-align: top; }
        .items { margin-top: 18px; }
        .items th { background: #f8fafc; border-bottom: 1px solid #dbe3ef; color: #475569; font-size: 10px; letter-spacing: .12em; padding: 9px; text-align: left; text-transform: uppercase; }
        .items td { border-bottom: 1px solid #e2e8f0; padding: 10px 9px; }
        .number { text-align: right; white-space: nowrap; }
        .summary { margin-top: 18px; }
        .payment-note { border: 1px solid #e2e8f0; border-radius: 6px; color: #475569; padding: 12px; }
        .totals { margin-left: auto; width: 250px; }
        .totals td { border-bottom: 1px solid #e2e8f0; padding: 7px 0; }
        .amount { font-weight: 700; text-align: right; white-space: nowrap; }
        .grand td { font-size: 14px; font-weight: 700; padding-top: 10px; }
        .terms { border-top: 1px solid #e2e8f0; color: #475569; margin-top: 18px; padding-top: 14px; }
        .footer { border-top: 1px solid #e2e8f0; color: #64748b; font-size: 9px; line-height: 1.6; margin-top: 18px; padding-top: 9px; text-align: center; }
        .footer strong { color: #334155; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td style="width: 50%;">@if ($canRenderLogo)<img src="{{ $pdfLogoFile }}" class="logo" alt="Logo">@endif</td>
            <td style="width: 50%;">
                <div class="title">Invoice</div>
                <div class="meta">
                    <strong>Invoice No:</strong> {{ $invoice->invoice_no }}<br>
                    <strong>Date:</strong> {{ $invoice->invoice_date?->format('d M Y') }}<br>
                    <strong>Due:</strong> {{ $invoice->due_date?->format('d M Y') ?: '-' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="bill">
        <tr>
            <td>
                <div class="label">Bill To</div>
                <strong>{{ $invoice->client?->name }}</strong><br>
                <span style="color:#475569;">{!! nl2br(e($invoice->client?->address)) !!}<br>{{ $invoice->client?->email }}<br>{{ $invoice->client?->phone }}</span>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead><tr><th>Item</th><th>Type</th><th class="number">Qty</th><th class="number">Rate</th><th class="number">Total</th></tr></thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td><strong>{{ $item->item_name }}</strong></td>
                    <td>{{ ucfirst($item->item_type) }}</td>
                    <td class="number">{{ number_format((float) $item->quantity, 2) }}</td>
                    <td class="number">{{ $currencySymbol }} {{ number_format((float) $item->rate, 2) }}</td>
                    <td class="number"><strong>{{ $currencySymbol }} {{ number_format((float) $item->line_total, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td style="width: 52%; padding-right: 18px;"><div class="payment-note"><strong>Payment</strong><br>Balance due: {{ $currencySymbol }} {{ number_format((float) $invoice->balance_due, 2) }}</div></td>
            <td style="width: 48%;">
                <table class="totals">
                    <tr><td>Subtotal</td><td class="amount">{{ $currencySymbol }} {{ number_format((float) $invoice->subtotal, 2) }}</td></tr>
                    <tr><td>Tax ({{ number_format((float) $invoice->tax_rate_percent, 4) }}%)</td><td class="amount">{{ $currencySymbol }} {{ number_format((float) $invoice->tax_amount, 2) }}</td></tr>
                    <tr><td>Paid</td><td class="amount">{{ $currencySymbol }} {{ number_format((float) $invoice->amount_paid, 2) }}</td></tr>
                    <tr class="grand"><td>Balance</td><td class="amount">{{ $currencySymbol }} {{ number_format((float) $invoice->balance_due, 2) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    @if ($invoice->termCondition)
        <div class="terms"><strong>{{ $invoice->termCondition->name }}</strong><br>{!! nl2br(e($invoice->termCondition->content)) !!}</div>
    @endif

    <div class="footer">
        @if ($company->company_email)
            <strong>Email:</strong> {{ $company->company_email }}
        @endif
        @if ($companyContact)
            @if ($company->company_email) &nbsp;|&nbsp; @endif
            <strong>Contact:</strong> {{ $companyContact }}
        @endif
        @if ($companyAddress)
            @if ($company->company_email || $companyContact) &nbsp;|&nbsp; @endif
            <strong>Address:</strong> {!! nl2br(e($companyAddress)) !!}
        @endif
        <br>
        This is a computer-generated invoice and does not require a physical signature.
    </div>
</body>
</html>
