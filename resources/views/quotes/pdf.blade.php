<!DOCTYPE html>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { margin: 0; padding: 0; background: #f1f5f9; }
</style>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Quote {{ $quote->quote_number }}</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                background: #f1f5f9;
                font-family: 'DejaVu Sans', sans-serif;
            }
            .page {
                width: 210mm;
                min-height: 297mm;
                margin: 0 auto;
                padding: 24px;
                box-sizing: border-box;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            .header-table {
                background: #1e2a3a;
                color: #ffffff;
                border-radius: 8px 8px 0 0;
            }
            .header-table td {
                padding: 20px;
                vertical-align: top;
            }
            .header-label {
                color: #94a3b8;
                font-size: 11px;
                letter-spacing: 1px;
                margin: 0 0 6px;
                text-transform: uppercase;
            }
            .header-number {
                margin: 0;
                font-size: 26px;
                font-weight: 700;
            }
            .header-meta {
                text-align: right;
                font-size: 12px;
                line-height: 1.6;
            }
            .info-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                margin-top: 0;
                margin-bottom: 16px;
            }
            .info-card td {
                padding: 16px;
                vertical-align: top;
                font-size: 12px;
                color: #64748b;
            }
            .info-card td:first-child {
                width: 50%;
            }
            .info-card td.second-column {
                border-left: 1px solid #e2e8f0;
            }
            .info-label {
                color: #94a3b8;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin: 0 0 6px;
            }
            .info-bold {
                margin: 0;
                font-size: 13px;
                font-weight: 700;
                color: #1e2a3a;
            }
            .items-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 16px;
                margin-bottom: 16px;
            }
            .items-label {
                color: #94a3b8;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 10px;
            }
            .items-table {
                width: 100%;
                border-collapse: collapse;
            }
            .items-table th,
            .items-table td {
                padding: 8px 12px;
                font-size: 12px;
            }
            .items-table th {
                background: #1e2a3a;
                color: #ffffff;
                text-transform: uppercase;
                font-size: 11px;
            }
            .items-table td {
                color: #1e293b;
                border-bottom: 1px solid #f1f5f9;
            }
            .items-table td.amount {
                text-align: right;
            }
            .items-table td.discount {
                text-align: center;
            }
            .summary-wrapper {
                margin-bottom: 16px;
            }
            .summary-table {
                width: 100%;
                border-collapse: collapse;
            }
            .summary-table td {
                vertical-align: top;
                padding: 0;
            }
            .summary-card {
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                background: #ffffff;
                padding: 16px;
            }
            .summary-label {
                color: #94a3b8;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin: 0 0 10px;
            }
            .fin-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 11px;
                color: #1e293b;
            }
            .fin-table td {
                padding: 4px 0;
            }
            .fin-table td.label {
                font-weight: 600;
            }
            .fin-table td.value {
                text-align: right;
            }
            .fin-table td.taxable {
                font-weight: 700;
            }
            .fin-table td.tax {
                color: #94a3b8;
                font-size: 10px;
            }
            .fin-divider {
                border-top: 1px solid #e5e7eb;
                margin: 6px 0;
            }
            .grand-total {
                font-size: 16px;
                font-weight: 700;
                color: #1e2a3a;
            }
            .bottom-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 16px;
            }
            .bottom-card td {
                padding: 0 16px;
                vertical-align: top;
                font-size: 12px;
                color: #334155;
            }
            .bottom-card td:not(:first-child) {
                border-left: 1px solid #e2e8f0;
                padding-left: 16px;
            }
            .bottom-card .bottom-label {
                color: #94a3b8;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 6px;
            }
        </style>
    </head>
    <body>
        @php
            $companyName    = config('company.name', 'Invoice Pro');
            $companyAddress = config('company.address', 'Nil Madhav Nagar');
            $companyGstin   = config('company.gstin', '22ABCDE1234F1Z5');
        @endphp
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { margin: 0; padding: 0; }
        </style>
        <div class="page" style="width:100%; margin:0; padding:0; font-family:'DejaVu Sans',sans-serif; background:#f1f5f9;">
            <table class="header-table" width="100%" style="table-layout:fixed; width:100%; background:#1e2a3a; padding:20px; border-radius:0; margin:0 0 16px 0;">
                <tr>
                    <td width="45%" style="word-wrap:break-word; overflow:hidden;">
                            <p class="header-label">QUOTE</p>
                            <h1 class="header-number">{{ $quote->quote_number }}</h1>
                    </td>
                    <td width="55%" style="text-align:right; padding-right:8px; word-wrap:break-word; overflow:hidden;">
                        <p class="header-meta">Status: {{ $quote->status ?? '—' }}</p>
                        <p class="header-meta">Issued On: {{ \Carbon\Carbon::parse($quote->issued_on)->format('d/m/Y') }}</p>
                        <p class="header-meta">Valid Till: {{ \Carbon\Carbon::parse($quote->valid_till)->format('d/m/Y') }}</p>
                    </td>
                </tr>
            </table>
            <div style="padding:16px;">

            <table class="info-card" width="100%" style="table-layout:fixed;">
                <tr>
                    <td width="50%" style="word-wrap:break-word; overflow:hidden;">
                        <p class="info-label">From</p>
                        <p class="info-bold">{{ $companyName }}</p>
                        <p>{{ $companyAddress }}</p>
                        <p>GSTIN: {{ $companyGstin }}</p>
                    </td>
                    <td class="second-column" width="50%" style="word-wrap:break-word; overflow:hidden;">
                        <p class="info-label">Bill To</p>
                        <p class="info-bold">{{ $quote->client->name ?? '—' }}</p>
                        <p>{{ $quote->client->address ?? '—' }}</p>
                        <p>Email: {{ $quote->client->email ?? '—' }}</p>
                        <p>Phone: {{ $quote->client->phone ?? '—' }}</p>
                    </td>
                </tr>
            </table>

            <div class="items-card">
                <p class="items-label">Items</p>
                <table class="items-table" width="100%" style="table-layout:fixed;">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Rate</th>
                            <th class="discount">Discount %</th>
                            <th class="amount">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quote->items as $item)
                            <tr>
                                <td style="word-wrap:break-word; overflow:hidden;">{{ $item->name }}</td>
                                <td style="word-wrap:break-word; overflow:hidden;">{{ number_format($item->qty ?? 0, 3) }}</td>
                                <td style="word-wrap:break-word; overflow:hidden;">Rs{{ number_format($item->rate ?? 0, 2) }}</td>
                                <td class="discount" style="word-wrap:break-word; overflow:hidden;">{{ number_format($item->discount_percent ?? 0, 2) }}%</td>
                                <td class="amount" style="word-wrap:break-word; overflow:hidden;">Rs{{ number_format($item->amount ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="summary-wrapper">
                <table width="100%" style="table-layout:fixed; width:100%;">
                    <tr>
                        <td width="55%" style="word-wrap:break-word; overflow:hidden;"></td>
                        <td width="45%" style="vertical-align:top; padding-left:8px;">
                            <div class="summary-card">
                                <p class="summary-label">Financial Summary</p>
                                @foreach ([
                                    ['label' => 'Subtotal', 'value' => $quote->subtotal ?? 0, 'style' => 'font-size:12px; color:#334155;'],
                                    ['label' => 'Discount', 'value' => $quote->discount ?? 0, 'style' => 'font-size:12px; color:#334155;'],
                                    ['label' => 'Taxable Amount', 'value' => $quote->taxable_amount ?? 0, 'style' => 'font-size:12px; color:#334155; font-weight:700;'],
                                    ['label' => 'CGST', 'value' => $quote->cgst ?? 0, 'style' => 'font-size:12px; color:#94a3b8;'],
                                    ['label' => 'SGST', 'value' => $quote->sgst ?? 0, 'style' => 'font-size:12px; color:#94a3b8;'],
                                    ['label' => 'IGST', 'value' => $quote->igst ?? 0, 'style' => 'font-size:12px; color:#94a3b8;'],
                                    ['label' => 'Round Off', 'value' => $quote->round_off ?? 0, 'style' => 'font-size:12px; color:#334155;'],
                                ] as $row)
                                    <table width="100%" style="table-layout:fixed; width:100%; margin-bottom:4px;">
                                        <tr>
                                            <td width="55%" style="font-size:12px; color:#334155; padding:3px 0; {{ $row['style'] }};">
                                                {{ $row['label'] }}
                                            </td>
                                            <td width="45%" style="text-align:right; padding-right:6px; font-size:12px; color:#334155; word-wrap:break-word;">
                                                Rs{{ number_format($row['value'], 2) }}
                                            </td>
                                        </tr>
                                    </table>
                                @endforeach
                                <table width="100%" style="table-layout:fixed; width:100%; margin-bottom:4px;">
                                    <tr>
                                        <td colspan="2">
                                            <hr style="border:1px solid #e2e8f0;">
                                        </td>
                                    </tr>
                                </table>
                                <table width="100%" style="table-layout:fixed; width:100%;">
                                    <tr>
                                        <td width="55%" style="font-size:15px; font-weight:bold; color:#1e2a3a;">Grand Total</td>
                                        <td width="45%" style="text-align:right; padding-right:6px; font-size:15px; font-weight:bold; color:#1e2a3a;">
                                            Rs{{ number_format($quote->grand_total ?? 0, 2) }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <table class="bottom-card" width="100%" style="table-layout:fixed;">
                <tr>
                    <td width="33%" style="word-wrap:break-word; overflow:hidden;">
                        <p class="bottom-label">Notes</p>
                        <p>{{ $quote->notes ?? '—' }}</p>
                    </td>
                    <td width="33%" style="word-wrap:break-word; overflow:hidden;">
                        <p class="bottom-label">Payment Terms</p>
                        <p>{{ $quote->payment_terms ?? '—' }}</p>
                    </td>
                    <td width="34%" style="word-wrap:break-word; overflow:hidden;">
                        <p class="bottom-label">Terms &amp; Conditions</p>
                        <p>{{ $quote->terms_conditions ?? 'No additional terms.' }}</p>
                    </td>
                </tr>
            </table>
            </div>
        </div>
    </body>
</html>
