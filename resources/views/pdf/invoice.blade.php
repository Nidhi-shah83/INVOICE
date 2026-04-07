<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Invoice {{ $invoice->invoice_number }}</title>
        <style>
            @page {
                margin: 20mm 15mm;
            }

            body {
                margin: 0;
                font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
                background: #f3f4f6;
                color: #1e293b;
                line-height: 1.4;
                font-size: 11px;
            }

            .page {
                background: #fff;
                padding: 0;
                min-height: 297mm;
            }

            .watermark {
                position: fixed;
                top: 40%;
                left: 0;
                right: 0;
                font-size: 64px;
                text-align: center;
                color: rgba(75, 85, 99, 0.08);
                transform: rotate(-25deg);
                z-index: 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            .header {
                background: #1e293b;
                color: #fff;
                padding: 4px;
            }

            .header td {
                padding: 16px;
                vertical-align: middle;
            }

            .header h1 {
                margin: 0;
                font-size: 26px;
                letter-spacing: 2px;
            }

            .status-pill {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 999px;
                font-size: 10px;
                letter-spacing: 0.4px;
                text-transform: uppercase;
                background: #fbbf24;
                color: #7f1d1d;
            }

            .status-pill.paid {
                background: #4ade80;
                color: #14532d;
            }

            .status-pill.overdue {
                background: #f87171;
                color: #7f1d1d;
            }

            .meta-line {
                font-size: 12px;
                color: rgba(255, 255, 255, 0.8);
                margin-top: 6px;
            }

            .section {
                padding: 20px 30px;
            }

            .info-table td {
                width: 33%;
                padding: 6px;
            }

            .info-card {
                background: #f1f5f9;
                border-radius: 8px;
                padding: 10px 12px;
                border-left: 4px solid #1a3c5e;
            }

            .info-card strong {
                display: block;
                font-size: 10px;
                letter-spacing: 1px;
                text-transform: uppercase;
                color: #64748b;
                margin-bottom: 6px;
            }

            .items-table {
                margin-top: 16px;
            }

            .items-table thead th {
                background: #1a3c5e;
                color: #fff;
                padding: 10px 8px;
                font-size: 10px;
                letter-spacing: 1px;
                text-transform: uppercase;
                text-align: left;
            }

            .items-table tbody td {
                padding: 10px 8px;
                font-size: 10px;
                border-bottom: 1px solid #e2e8f0;
            }

            .items-table tbody tr:nth-child(odd) {
                background: #f8f9fc;
            }

            .items-table td.numeric {
                text-align: center;
            }

            .summary-wrapper td {
                vertical-align: top;
                padding-top: 12px;
            }

            .summary-card {
                border: 1px solid #cbd5f5;
                border-radius: 10px;
                padding: 12px 14px;
                background: #fff;
                box-shadow: 0 12px 36px rgba(15, 23, 42, 0.08);
                page-break-inside: avoid;
            }

            .summary-card h3 {
                margin: 0 0 10px;
                font-size: 13px;
                border-bottom: 1px solid #cbd5f5;
                padding-bottom: 6px;
                color: #1a3c5e;
            }

            .summary-card table td {
                padding: 6px 0;
                font-size: 11px;
            }

            .summary-card .label {
                color: #64748b;
            }

            .summary-card .value {
                text-align: right;
                font-weight: 600;
            }

            .grand-total-row {
                background: #e8f0fe;
                border-radius: 8px;
                padding: 10px;
                font-weight: 700;
                margin-top: 10px;
            }

            .amount-paid {
                color: #047857;
                margin-top: 8px;
            }

            .balance-due {
                margin-top: 6px;
                font-weight: 700;
                padding: 6px 8px;
                border-radius: 6px;
            }

            .balance-due.positive {
                background: #fee2e2;
                color: #b91c1c;
            }

            .balance-due.zero {
                background: #dcfce7;
                color: #047857;
            }

            .notes-terms-table td {
                padding: 10px;
                vertical-align: top;
            }

            .notes-terms strong {
                display: block;
                font-size: 11px;
                letter-spacing: 1px;
                text-transform: uppercase;
                color: #1a3c5e;
                margin-bottom: 4px;
            }

            .signature-wrapper {
                margin-top: 8px;
            }

            .signature-line {
                border-bottom: 1px solid #94a3b8;
                margin-bottom: 4px;
            }

            .signature-label {
                font-size: 10px;
                letter-spacing: 0.6px;
                color: #64748b;
            }

            .footer {
                margin-top: 40px;
                border-top: 1px solid #e2e8f0;
                padding: 12px 30px;
                font-size: 10px;
                color: #64748b;
            }

            .footer div {
                display: inline-block;
                width: 33%;
                vertical-align: top;
            }

            .footer .center {
                text-align: center;
            }

            .footer .right {
                text-align: right;
            }

            .footer .muted {
                font-size: 8px;
                color: #94a3b8;
                margin-top: 4px;
            }
        </style>
    </head>
    <body>
        <div class="page">
            
            <table class="header">
                <tbody>
                    <tr>
                        <td>
                            <h1>INVOICE</h1>
                            <span style="font-size: 14px; letter-spacing: 0.5px;">{{ $invoice->invoice_number }}</span>
                        </td>
                        <td style="text-align: right;">
                            @php
                                $statusClass = match($invoice->status) {
                                    'paid' => 'status-pill paid',
                                    'overdue' => 'status-pill overdue',
                                    default => 'status-pill',
                                };
                            @endphp
                            
                            <div class="meta-line">
                                Date: {{ $invoice->issue_date?->format('d M, Y') ?? '—' }}
                            </div>
                            <div class="meta-line">
                                Due: {{ $invoice->due_date?->format('d M, Y') ?? '—' }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="section">
                <table class="info-table">
                    <tbody>
                        <tr>
                            <td>
                                <div class="info-card">
                                    <strong>From</strong>
                                    <div>{{ setting('business_name', 'Invoice Pro') }}</div>
                                    <div>{{ setting('address', '123 Business Street') }}</div>
                                    <div>GSTIN {{ setting('gstin', '—') }}</div>
                                    <div>{{ setting('email', 'hello@example.com') }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="info-card">
                                    <strong>Bill To</strong>
                                    <div>{{ $invoice->client->name ?? '—' }}</div>
                                    <div>{{ $invoice->client->address ?? '—' }}</div>
                                    <div>{{ $invoice->client->email ?? '—' }}</div>
                                    <div>GSTIN {{ $invoice->client->gstin ?? '—' }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="info-card">
                                    <strong>Ship To</strong>
                                    <div>{{ $invoice->ship_to_name ?? $invoice->client->name ?? '—' }}</div>
                                    <div>{{ $invoice->ship_to_address ?? $invoice->client->address ?? '—' }}</div>
                                    <div>GSTIN {{ $invoice->ship_to_gstin ?? $invoice->client->gstin ?? '—' }}</div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="numeric">Qty</th>
                            <th class="numeric">Rate</th>
                            <th class="numeric">Disc %</th>
                            <th class="numeric">Tax %</th>
                            <th class="numeric">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td class="numeric">{{ number_format($item->qty_billed ?? $item->quantity ?? 0, 2) }}</td>
                                <td class="numeric">₹{{ number_format($item->rate ?? 0, 2) }}</td>
                                <td class="numeric">{{ number_format($item->discount_percent ?? 0, 2) }}</td>
                                <td class="numeric">{{ number_format($item->gst_percent ?? 0, 2) }}</td>
                                <td class="numeric">₹{{ number_format($item->amount ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <table class="summary-wrapper">
                    <tbody>
                        <tr>
                            <td></td>
                            <td style="width: 40%; text-align: right;">
                                <div class="summary-card">
                                    <h3>Totals</h3>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <td class="label">Subtotal</td>
                                                <td class="value">₹{{ number_format($invoice->subtotal ?? 0, 2) }}</td>
                                            </tr>
                                            @if($invoice->discount_amount > 0)
                                                <tr>
                                                    <td class="label">Discount</td>
                                                    <td class="value">₹{{ number_format($invoice->discount_amount, 2) }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td class="label">Taxable Amount</td>
                                                <td class="value">₹{{ number_format(max(0, ($invoice->subtotal ?? 0) - ($invoice->discount_amount ?? 0)), 2) }}</td>
                                            </tr>
                                            @if($invoice->cgst > 0)
                                                <tr>
                                                    <td class="label">CGST</td>
                                                    <td class="value">₹{{ number_format($invoice->cgst, 2) }}</td>
                                                </tr>
                                            @endif
                                            @if($invoice->sgst > 0)
                                                <tr>
                                                    <td class="label">SGST</td>
                                                    <td class="value">₹{{ number_format($invoice->sgst, 2) }}</td>
                                                </tr>
                                            @endif
                                            @if($invoice->igst > 0)
                                                <tr>
                                                    <td class="label">IGST</td>
                                                    <td class="value">₹{{ number_format($invoice->igst, 2) }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td class="label">Round Off</td>
                                                <td class="value">₹{{ number_format($invoice->round_off ?? 0, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="grand-total-row">
                                        <div style="display: table; width: 100%;">
                                            <span style="display: table-cell;">Grand Total</span>
                                            <span style="display: table-cell; text-align: right;">
                                                ₹{{ number_format($invoice->grand_total ?? $invoice->total ?? 0, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="notes-terms-table" style="margin-top: 16px;">
                    <tbody>
                        <tr>
                            <td width="45%">
                                <strong>Notes</strong>
                                <p>{{ $invoice->notes ?? 'No additional notes.' }}</p>
                            </td>
                            <td width="35%">
                                <strong>Terms &amp; Conditions</strong>
                                <p>{{ $invoice->terms_conditions ?? 'As agreed' }}</p>
                            </td>
                            <td width="10%">
                                <strong>Signature</strong>
                                <div wire:click="sign" class="signature-wrapper">
                                    <div class="signature-line"></div>
                                    
                                    
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            
        </div>
    </body>
</html>
