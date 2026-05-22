@php
    $logoPath = $company->company_logo_web_path ?: 'assets/images/hariman-nexus-wordmark.png';
    $logoFile = public_path($logoPath);
    $logoInfo = pathinfo($logoFile);
    $jpegFallback = ($logoInfo['dirname'] ?? '').DIRECTORY_SEPARATOR.($logoInfo['filename'] ?? 'logo').'-pdf.jpg';
    $pdfLogoFile = is_file($jpegFallback) ? $jpegFallback : $logoFile;
    $canRenderLogo = is_file($pdfLogoFile) && (extension_loaded('gd') || in_array(strtolower(pathinfo($pdfLogoFile, PATHINFO_EXTENSION)), ['jpg', 'jpeg'], true));
    $currencySymbol = $quotation->currency?->symbol ?: $quotation->currency?->code;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4; margin: 8mm; }
        * { box-sizing: border-box; }
        body {
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
        }
        .header-table,
        .info-table,
        .summary-table {
            border-collapse: collapse;
            width: 100%;
        }
        .header-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 0 0 12px;
            vertical-align: top;
        }
        .logo {
            display: block;
            max-height: 90px;
            max-width: 300px;
        }
        .title {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: .04em;
            text-align: right;
            text-transform: uppercase;
        }
        .meta {
            color: #475569;
            line-height: 1.8;
            margin-top: 8px;
            text-align: right;
        }
        .info-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 0 16px;
            vertical-align: top;
        }
        .label {
            color: #475569;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .14em;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .client-name {
            font-size: 12px;
            font-weight: 700;
        }
        .muted {
            color: #475569;
        }
        .items {
            border-collapse: collapse;
            margin-top: 18px;
            width: 100%;
        }
        .items th {
            background: #f8fafc;
            border-bottom: 1px solid #dbe3ef;
            color: #475569;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .12em;
            padding: 9px;
            text-align: left;
            text-transform: uppercase;
        }
        .items td {
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 9px;
            vertical-align: top;
        }
        .number {
            text-align: right;
            white-space: nowrap;
        }
        .summary-table {
            margin-top: 18px;
        }
        .summary-table td {
            vertical-align: top;
        }
        .commercial-note {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            color: #475569;
            padding: 12px;
            width: 100%;
        }
        .commercial-note strong {
            color: #111827;
        }
        .totals {
            border-collapse: collapse;
            margin-left: auto;
            width: 250px;
        }
        .totals td {
            border-bottom: 1px solid #e2e8f0;
            padding: 7px 0;
        }
        .totals .amount {
            font-weight: 700;
            text-align: right;
            white-space: nowrap;
        }
        .totals .grand td {
            color: #111827;
            font-size: 14px;
            font-weight: 700;
            padding-top: 10px;
        }
        .terms {
            border-top: 1px solid #e2e8f0;
            color: #475569;
            margin-top: 18px;
            padding-top: 14px;
        }
        .terms-title {
            color: #111827;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .12em;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .footer-note {
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 9px;
            margin-top: 18px;
            padding-top: 9px;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                @if ($canRenderLogo)
                    <img src="{{ $pdfLogoFile }}" class="logo" alt="Logo">
                @endif
            </td>
            <td style="width: 50%;">
                <div class="title">Quotation</div>
                <div class="meta">
                    <strong>Quotation No:</strong> {{ $quotation->quotation_no }}<br>
                    <strong>Date:</strong> {{ $quotation->quotation_date?->format('d M Y') }}<br>
                    <strong>Valid Until:</strong> {{ $quotation->validity_date?->format('d M Y') ?: '-' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td style="width: 60%;">
                <div class="label">Prepared For</div>
                <div class="client-name">{{ $quotation->client?->name }}</div>
                <div class="muted">
                    {{ $quotation->client?->client_code }}<br>
                    {!! nl2br(e($quotation->client?->address)) !!}<br>
                    {{ $quotation->client?->email }}<br>
                    {{ $quotation->client?->phone }}
                </div>
            </td>
            <td style="width: 40%;"></td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th>Type</th>
                <th class="number">Qty</th>
                <th class="number">Rate</th>
                <th class="number">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quotation->items as $item)
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

    <table class="summary-table">
        <tr>
            <td style="width: 52%; padding-right: 18px;">
                <div class="commercial-note">
                    <strong>Commercial Note</strong><br>
                    This quotation remains valid until {{ $quotation->validity_date?->format('d M Y') ?: '-' }}.
                </div>
            </td>
            <td style="width: 48%;">
                <table class="totals">
                    <tr>
                        <td>Subtotal</td>
                        <td class="amount">{{ $currencySymbol }} {{ number_format((float) $quotation->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Tax ({{ number_format((float) $quotation->tax_rate_percent, 4) }}%)</td>
                        <td class="amount">{{ $currencySymbol }} {{ number_format((float) $quotation->tax_amount, 2) }}</td>
                    </tr>
                    <tr class="grand">
                        <td>Total</td>
                        <td class="amount">{{ $currencySymbol }} {{ number_format((float) $quotation->total, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if ($quotation->termCondition)
        <div class="terms">
            <div class="terms-title">{{ $quotation->termCondition->name }}</div>
            {!! nl2br(e($quotation->termCondition->content)) !!}
        </div>
    @endif

    <div class="footer-note">This is a computer-generated quotation and does not require a physical signature.</div>
</body>
</html>
