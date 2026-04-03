<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Quote {{ $quote->quote_number }}</title>
        <style>
            @page {
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
                background: #f4f5f7;
                font-family: 'DejaVu Sans', Arial, sans-serif;
            }
            .page {
                width: 210mm;
                margin: 0 auto;
                padding: 24px;
            }
            .card {
                background: #ffffff;
                border-radius: 28px;
                overflow: hidden;
                box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
            }
            .header {
                background: linear-gradient(135deg, #0f172a, #1e293b);
                color: #f8fafc;
                padding: 32px;
            }
            .header span {
                display: block;
                font-size: 11px;
                letter-spacing: 0.4em;
                text-transform: uppercase;
                color: rgba(248, 250, 252, 0.8);
                margin-bottom: 8px;
            }
            .header h1 {
                margin: 0;
                font-size: 32px;
                font-weight: 600;
            }
            .header-meta {
                margin-top: 12px;
                font-size: 11px;
                letter-spacing: 0.2em;
                text-transform: uppercase;
                color: rgba(248, 250, 252, 0.85);
            }
            .section-row {
                display: table;
                width: 100%;
                padding: 32px 32px 0;
            }
            .box {
                display: table-cell;
                width: 50%;
                padding: 12px 16px;
                background: #f8fafc;
                border-radius: 16px;
                margin-right: 12px;
            }
            .box:last-child {
                margin-right: 0;
            }
            .box h4 {
                margin: 0 0 8px;
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.4em;
                color: #6b7280;
            }
            .box p {
                margin: 2px 0;
                font-size: 13px;
                color: #0f172a;
            }
            .items {
                padding: 24px 32px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 12px;
            }
            table thead th {
                background: #0f172a;
                color: #ffffff;
                letter-spacing: 0.3em;
                text-transform: uppercase;
                font-size: 10px;
                padding: 10px 8px;
            }
            tbody td {
                padding: 12px 8px;
                border-bottom: 1px solid #e2e8f0;
            }
            .amount {
                text-align: right;
                font-weight: 600;
            }
            .summary-wrapper {
                padding: 0 32px 32px;
                display: table;
                width: 100%;
            }
            .summary-card {
                width: 280px;
                margin-left: auto;
                background: #f8fafc;
                border-radius: 24px;
                padding: 20px;
                border: 1px solid #e2e8f0;
            }
            .summary-row {
                display: flex;
                justify-content: space-between;
                font-size: 12px;
                margin-bottom: 6px;
            }
            .summary-divider {
                border-top: 1px dashed #cbd5f5;
                margin: 12px 0;
            }
            .grand {
                display: flex;
                justify-content: space-between;
                font-size: 16px;
                font-weight: 700;
            }
            .notes-block {
                padding: 0 32px 32px;
            }
            .notes-grid {
                display: table;
                width: 100%;
                border-spacing: 16px;
            }
            .notes-grid div {
                display: table-cell;
                background: #f8fafc;
                border-radius: 16px;
                padding: 16px;
                font-size: 12px;
            }
            .notes-grid p.label {
                margin: 0 0 6px;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 0.3em;
                color: #6b7280;
            }

            /* force single page layout if possible */
            .page {
                width: 210mm;
                min-height: 297mm;
                padding: 14px;
                page-break-after: avoid;
                page-break-inside: avoid;
            }

            .card, .items, .summary-wrapper, .notes-block {
                page-break-inside: avoid;
            }

            table, thead, tbody, tr, td, th {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            td, th {
                font-size: 11px;
                padding: 8px;
            }

            .header h1 {
                font-size: 26px;
            }

            .summary-card {
                width: 100%;
                max-width: 280px;
            }
        </style>
    </head>
    <body>
        @php
            $currencySymbol = config('invoice.currency_symbol', '₹');
            $formatted = fn ($value) => $currencySymbol . number_format($value, 2);
            $taxableAmount = $quote->subtotal - $quote->discount_amount;
        @endphp

        <div class="page">
            <div class="card">
                <div class="header">
                    <span>Quote</span>
                    <h1>{{ $quote->quote_number }}</h1>
                    <div class="header-meta">
                        Status: {{ ucfirst($quote->status) }} · Issued On: {{ $quote->issue_date->format('d M, Y') }} · Valid Till: {{ $quote->validity_date->format('d M, Y') }}
                    </div>
                </div>

                <div class="section-row">
                    <div class="box">
                        <h4>From</h4>
                        <p><strong>{{ config('invoice.business_name') }}</strong></p>
                        <p>{{ config('invoice.address_line') ?? '123 Corporate Blvd, City, State ZIP' }}</p>
                        <p>GSTIN {{ config('invoice.gstin') }}</p>
                        <p>{{ config('invoice.email') }}</p>
                        <p>{{ config('invoice.phone') }}</p>
                    </div>
                    <div class="box">
                        <h4>Bill To</h4>
                        <p><strong>{{ $quote->client->name }}</strong></p>
                        <p>{{ $quote->client->address }}</p>
                        @if($quote->client->gstin)
                            <p>GSTIN {{ $quote->client->gstin }}</p>
                        @endif
                        @if($quote->client->email)
                            <p>Email: {{ $quote->client->email }}</p>
                        @endif
                        @if($quote->client->phone)
                            <p>Phone: {{ $quote->client->phone }}</p>
                        @endif
                    </div>
                </div>

                <div class="items">
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Rate</th>
                                <th>Discount %</th>
                                <th class="amount">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quote->items as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ number_format($item->qty, 3) }}</td>
                                    <td>{{ $currencySymbol }}{{ number_format($item->rate, 2) }}</td>
                                    <td>{{ number_format($item->discount_percent ?? 0, 2) }}%</td>
                                    <td class="amount">{{ $currencySymbol }}{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="summary-wrapper">
                    <div class="summary-card">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>{{ $formatted($quote->subtotal) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Discount</span>
                            <span>{{ $formatted($quote->discount_amount) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Taxable Amount</span>
                            <span>{{ $formatted($taxableAmount) }}</span>
                        </div>
                        @foreach(['CGST','SGST','IGST'] as $label)
                            <div class="summary-row">
                                <span>{{ $label }}</span>
                                <span>{{ $formatted($quote->{strtolower($label)}) }}</span>
                            </div>
                        @endforeach
                        <div class="summary-row">
                            <span>Round Off</span>
                            <span>{{ $formatted($quote->round_off) }}</span>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="grand">
                            <span>Grand Total</span>
                            <span>{{ $formatted($quote->grand_total) }}</span>
                        </div>
                    </div>
                </div>

                <div class="notes-block">
                    <div class="notes-grid">
                        <div>
                            <p class="label">Notes</p>
                            <p>{{ $quote->notes ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="label">Payment Terms</p>
                            <p>{{ $quote->payment_terms ?? 'Payment due within 15 days of acceptance' }}</p>
                        </div>
                        <div>
                            <p class="label">Terms & Conditions</p>
                            <p>{{ $quote->terms_conditions ?? 'No additional terms.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
