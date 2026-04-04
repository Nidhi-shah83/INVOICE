<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Order {{ $order->order_number }}</title>
        <style>
            @page {
                margin: 0;
            }

            body {
                margin: 0;
                padding: 0;
                background: #f4f5f7;
                font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            }

            .page {
                width: 210mm;
                min-height: 230mm;
                padding: 0;
                box-sizing: border-box;
                display: table;
            }

            table {
                border-collapse: collapse;
                width: 100%;
            }

            .card {
                background: #ffffff;
                border-radius: 0;
                overflow: hidden;
                border: none;
                padding: 0;
                box-shadow: none;
                width: 100%;
                min-height: 230mm;
            }

            .header-table {
                background: #1e2a3a;
                color: #f8fafc;
                padding: 24px;
            }

            .header-table td {
                vertical-align: top;
                padding: 24px;
            }

            .label {
                display: block;
                font-size: 11px;
                letter-spacing: 0.4em;
                text-transform: uppercase;
                color: rgba(248, 250, 252, 0.8);
                margin-bottom: 6px;
            }

            .quote-number {
                margin: 0;
                font-size: 30px;
                font-weight: 700;
            }

            .meta-table {
                width: 100%;
                border-collapse: collapse;
            }

            .meta-table td {
                padding: 0;
                vertical-align: top;
            }

            .meta-table tr:nth-child(n+2) td {
                padding-top: 4px;
            }

            .meta-label {
                font-size: 9px;
                letter-spacing: 2px;
                text-transform: uppercase;
                color: #8a9bb0;
                padding-right: 10px;
            }

            .meta-value {
                font-size: 11px;
                letter-spacing: 1px;
                text-transform: uppercase;
                font-weight: 700;
                color: #ffffff;
                text-align: right;
            }

            .section-table td {
                vertical-align: top;
                padding: 20px 24px 8px;
            }

            .section-box {
                border: 1px solid #dfe4ec;
                border-radius: 12px;
                padding: 12px 14px;
                background: #fdfdff;
            }

            .section-title {
                margin: 0;
                font-size: 9px;
                letter-spacing: 0.4em;
                text-transform: uppercase;
                color: #6b7280;
            }

            .section-text {
                margin: 4px 0;
                font-size: 12px;
                line-height: 1.4;
                color: #0f172a;
            }

            .items-summary {
                padding: 0 24px 0;
            }

            .items-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 11px;
                table-layout: fixed;
                border: 1px solid #dfe4ec;
            }

            .items-table thead th {
                background: #1e2a3a;
                color: #ffffff;
                font-size: 9px;
                text-transform: uppercase;
                letter-spacing: 0.2em;
                padding: 10px 6px;
                border-right: 1px solid #08101a;
            }

            .items-table tbody td {
                border-bottom: 1px solid #e5e7eb;
                padding: 10px 6px;
                color: #111827;
                background: #ffffff;
            }

            .items-table td.amount,
            .items-table th.amount {
                text-align: right;
            }

            .items-table td,
            .items-table th {
                border-right: 1px solid #dfe4ec;
            }

            .items-table th:last-child,
            .items-table td:last-child {
                border-right: none;
            }

            .summary-panel {
                width: 100%;
                border-radius: 14px;
                border: none;
                background: #f9fafc;
                padding: 16px 16px 8px;
                font-size: 12px;
                border-collapse: collapse;
                margin-top: 16px;
            }

            .summary-panel td {
                font-size: 11px;
                padding: 6px 0;
                vertical-align: top;
            }

            .summary-label {
                width: 55%;
                color: #4b5563;
                font-weight: 500;
            }

            .summary-value {
                width: 45%;
                text-align: right;
                font-weight: 600;
            }

            .summary-bold {
                font-weight: 700;
                color: #1e2a3a;
            }

            .summary-hr {
                border-top: 1px dashed #c5cdd8;
            }

            .summary-hr.solid {
                border-top: 1.5px solid #1e2a3a;
            }

            .grand-total {
                font-size: 13px;
                letter-spacing: 0.2em;
                text-transform: uppercase;
                font-weight: 700;
                color: #1e2a3a;
            }

            .footer-table td {
                width: 33.33%;
                padding: 18px;
                border: 1px solid #e2e8f0;
                background: #fdfdff;
                vertical-align: top;
                border-radius: 12px;
            }

            .footer-label {
                font-size: 9px;
                text-transform: uppercase;
                letter-spacing: 0.3em;
                color: #6b7280;
                margin-bottom: 6px;
            }

            .footer-text {
                font-size: 11px;
                line-height: 1.4;
                color: #111827;
            }

            .signature-table {
                padding: 0 24px 24px;
                margin-top: 12px;
                width: 100%;
            }

            .signature-table td {
                padding: 12px 0;
            }

            .signature-cell {
                text-align: right;
            }

            .signature-box {
                width: 100%;
                height: 54px;
                margin-left: auto;
                border: 1px dashed #b0bac8;
                border-radius: 4px;
                margin-bottom: 8px;
            }

            .signature-label {
                text-align: right;
                font-size: 8px;
                letter-spacing: 2px;
                text-transform: uppercase;
                color: #7a8fa6;
                margin-bottom: 4px;
            }

            .signature-name {
                margin: 0;
                font-size: 10px;
                font-weight: 700;
                color: #1e2a3a;
            }
        </style>
    </head>
    <body>
        @php
            $currencySymbol = config('invoice.currency_symbol', '₹');
            $subtotal = $order->items->sum(fn ($item) => $item->qty * $item->rate);
            $billed = $order->billed_amount;
            $remaining = $order->total_amount - $order->billed_amount;
            $status = ucfirst(str_replace('_', ' ', $order->status));
            $issuedOn = $order->created_at?->format('d M, Y') ?? '-';
            $dueDate = $order->quote?->validity_date?->format('d M, Y') ?? '-';
            $logo = $logo ?? null;
            if (! $logo && ! empty($settingsService->get('logo'))) {
                $logoPath = storage_path('app/public/' . $settingsService->get('logo'));
                if (file_exists($logoPath)) {
                    $logo = base64_encode(file_get_contents($logoPath));
                }
            }
            if (! $logo) {
                $logoPath = public_path('images/logo.png');
                if (file_exists($logoPath)) {
                    $logo = base64_encode(file_get_contents($logoPath));
                }
            }
        @endphp

        <div class="page">
            <table class="card" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <table class="header-table" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="header-left" width="60%">
                                    @if(!empty($logo))
                                        <img src="data:image/png;base64,{{ $logo }}" alt="logo" class="logo">
                                    @endif
                                    <span class="label">ORDER</span>
                                    <p class="quote-number">{{ $order->order_number }}</p>
                                </td>
                                <td class="header-right" width="40%">
                                    <table class="meta-table" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td class="meta-label">Status</td>
                                            <td class="meta-value">{{ $status }}</td>
                                        </tr>
                                        <tr>
                                            <td class="meta-label">Created</td>
                                            <td class="meta-value">{{ $issuedOn }}</td>
                                        </tr>
                                        <tr>
                                            <td class="meta-label">Due</td>
                                            <td class="meta-value">{{ $dueDate }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table class="section-table" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <table class="section-box" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td>
                                                <p class="section-title">From</p>
                                                <p class="section-text"><strong>{{ $settingsService->get('business_name', config('company.name')) }}</strong></p>
                                                <p class="section-text">{{ $settingsService->get('address', config('company.address')) }}</p>
                                                <p class="section-text">GSTIN {{ $settingsService->get('gstin', config('company.gstin')) ?? '—' }}</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td>
                                    <table class="section-box" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td>
                                                <p class="section-title">Bill To</p>
                                                <p class="section-text"><strong>{{ $order->client->name }}</strong></p>
                                                <p class="section-text">{{ $order->client->address }}</p>
                                                <p class="section-text">GSTIN {{ $order->client->gstin ?? '—' }}</p>
                                                <p class="section-text">Email: {{ $order->client->email ?? '—' }}</p>
                                                <p class="section-text">Phone: {{ $order->client->phone ?? '—' }}</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <div class="items-summary">
                            <table class="items-table" cellpadding="0" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Rate</th>
                                        <th>GST %</th>
                                        <th class="amount">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ number_format($item->qty, 2) }}</td>
                                            <td>{{ $currencySymbol }}{{ number_format($item->rate, 2) }}</td>
                                            <td>{{ number_format($item->gst_percent, 2) }}%</td>
                                            <td class="amount">{{ $currencySymbol }}{{ number_format($item->qty * $item->rate, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <table class="summary-wrapper" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="58%"></td>
                                <td width="42%">
                                    <table class="summary-panel" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td class="summary-label">Subtotal</td>
                                            <td class="summary-value"><span>&#8377;{{ number_format($subtotal, 2) }}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="summary-label">Billed</td>
                                            <td class="summary-value"><span>&#8377;{{ number_format($billed, 2) }}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="summary-label summary-bold">Remaining</td>
                                            <td class="summary-value summary-bold"><span>&#8377;{{ number_format($remaining, 2) }}</span></td>
                                        </tr>
                                        <tr class="summary-hr solid">
                                            <td colspan="2"></td>
                                        </tr>
                                        <tr>
                                            <td class="summary-label grand-total">Order Total</td>
                                            <td class="summary-value grand-total"><span>&#8377;{{ number_format($order->total_amount, 2) }}</span></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table class="footer-table" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <p class="footer-label">Notes</p>
                                    <p class="footer-text">{{ optional($order->quote)->notes ?? '—' }}</p>
                                </td>
                                <td>
                                    <p class="footer-label">Payment Terms</p>
                                    <p class="footer-text">{{ optional($order->quote)->payment_terms ?? 'Payment due within 15 days of acceptance.' }}</p>
                                </td>
                                <td>
                                    <p class="footer-label">Status</p>
                                    <p class="footer-text">{{ $status }}</p>
                                </td>
                            </tr>
                        </table>

                        <table class="signature-table" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="58%"></td>
                                <td width="42%" class="signature-cell">
                                    <p class="signature-label">Digital Signature</p>
                                    <div class="signature-box"></div>
                                    <p class="signature-name">Authorized Signatory</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
