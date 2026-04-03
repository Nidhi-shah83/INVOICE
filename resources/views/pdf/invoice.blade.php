<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <style>
            body {
                font-family: "Times New Roman", Times, serif;
                font-size: 15px;
                color: #111111;
                margin: 0;
                padding: 0;
            }

            .page {
                padding: 34px 40px;
            }

            .top {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 10px;
            }

            .top td {
                vertical-align: top;
            }

            .invoice-title {
                font-size: 52px;
                font-weight: 700;
                letter-spacing: 0.8px;
                margin: 0 0 10px;
            }

            .company-name {
                font-size: 40px;
                font-weight: 700;
                margin: 0 0 4px;
            }

            .muted-line {
                margin: 2px 0;
                font-size: 22px;
            }

            .logo-box {
                width: 190px;
                height: 120px;
                border: 1px solid #808080;
                text-align: center;
                font-size: 16px;
                font-weight: 700;
                line-height: 1.4;
                margin-left: auto;
                display: table;
            }

            .logo-box span {
                display: table-cell;
                vertical-align: middle;
            }

            .invoice-meta {
                margin-top: 12px;
                text-align: right;
                font-size: 14px;
                line-height: 1.35;
            }

            .pair-table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
                margin-top: 14px;
                margin-bottom: 16px;
            }

            .pair-table th {
                background: #d8d8d8;
                border: 1px solid #8f8f8f;
                text-align: left;
                padding: 3px 7px;
                font-size: 14px;
            }

            .pair-table td {
                border: 1px solid #8f8f8f;
                padding: 6px 7px;
                font-size: 14px;
                vertical-align: top;
                height: 94px;
            }

            .meta-strip {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
                margin-bottom: 12px;
            }
            /* force single page layout if possible */
            .page {
                width: 210mm;
                min-height: 297mm;
                padding: 14px;
                page-break-after: avoid;
                page-break-inside: avoid;
            }

            .logo-box,
            .pair-table,
            .meta-strip,
            .items,
            .summary {
                page-break-inside: avoid;
            }

            td,
            th {
                font-size: 11px;
                padding: 5px;
            }
            .meta-strip th {
                border: 1px solid #7f7f7f;
                background: #d8d8d8;
                text-align: center;
                font-size: 12px;
                padding: 4px 3px;
                font-weight: 700;
                letter-spacing: 0.35px;
            }

            .meta-strip td {
                border: 1px solid #7f7f7f;
                height: 24px;
                font-size: 12px;
                padding: 3px 5px;
                text-align: center;
            }

            .items {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            .items th {
                border: 1px solid #7f7f7f;
                background: #d8d8d8;
                text-align: center;
                padding: 4px 5px;
                font-size: 12px;
                letter-spacing: 0.4px;
            }

            .items td {
                border: 1px solid #7f7f7f;
                padding: 3px 6px;
                font-size: 12px;
                height: 20px;
            }

            .num {
                text-align: right;
                white-space: nowrap;
            }

            .summary {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
                margin-top: -1px;
            }

            .summary td {
                border: 1px solid #7f7f7f;
                font-size: 12px;
                padding: 3px 6px;
            }

            .summary .notes-title {
                background: #d8d8d8;
                font-size: 13px;
                font-weight: 700;
            }

            .summary .notes-body {
                height: 74px;
                vertical-align: top;
                font-size: 12px;
            }

            .summary .label {
                text-align: right;
                background: #f2f2f2;
                font-weight: 700;
                letter-spacing: 0.2px;
            }

            .summary .value {
                text-align: right;
                width: 135px;
            }

            .summary .total-row td {
                font-size: 15px;
                font-weight: 700;
            }

            .thanks {
                margin-top: 26px;
                text-align: center;
                font-size: 26px;
                font-style: italic;
            }
        </style>
    </head>
    <body>
        @php
            $currencySymbol = config('invoice.currency_symbol', 'Rs ');
            $lineCount = max(10, $invoice->items->count());
            $salesTax = (float) $invoice->cgst + (float) $invoice->sgst + (float) $invoice->igst;
            $issueDate = $invoice->issue_date?->format('F d, Y') ?? 'N/A';
            $specialNotes = trim((string) ($invoice->notes ?: ''));
            $terms = trim((string) ($invoice->terms_conditions ?: ''));
            $notesCombined = trim($specialNotes.' '.($terms !== '' ? '| '.$terms : ''));
        @endphp

        <div class="page">
            <table class="top">
                <tr>
                    <td>
                        <p class="invoice-title">INVOICE</p>
                        <p class="company-name">{{ config('invoice.business_name', 'Your Company Name') }}</p>
                        <p class="muted-line">{{ config('invoice.company_slogan', '[Your Company Slogan]') }}</p>
                        <p class="muted-line">{{ config('invoice.address', 'Company Address') }}</p>
                        <p class="muted-line">{{ config('invoice.city_state_zip', '[City, ST ZIP Code]') }}</p>
                        <p class="muted-line">Phone {{ config('invoice.phone', 'N/A') }}</p>
                    </td>
                    <td style="width: 220px;">
                        <div class="logo-box"><span>COMPANY<br>LOGO HERE</span></div>
                        <div class="invoice-meta">
                            <div><strong>INVOICE #</strong> {{ $invoice->invoice_number }}</div>
                            <div><strong>DATE:</strong> {{ strtoupper($issueDate) }}</div>
                        </div>
                    </td>
                </tr>
            </table>

            <table class="pair-table">
                <tr>
                    <th style="width:50%;">BILL TO:</th>
                    <th style="width:50%;">SHIP TO:</th>
                </tr>
                <tr>
                    <td>
                        {{ $invoice->client->name ?? '[Name]' }}<br>
                        {{ $invoice->client->company_name ?? '[Company Name]' }}<br>
                        {{ $invoice->client->address ?? '[Street Address]' }}<br>
                        {{ $invoice->client->city ?? '[City, ST ZIP Code]' }}<br>
                        {{ $invoice->client->phone ?? '[Phone]' }}
                    </td>
                    <td>
                        {{ $invoice->client->name ?? '[Name]' }}<br>
                        {{ $invoice->client->company_name ?? '[Company Name]' }}<br>
                        {{ $invoice->client->address ?? '[Street Address]' }}<br>
                        {{ $invoice->client->city ?? '[City, ST ZIP Code]' }}<br>
                        {{ $invoice->client->phone ?? '[Phone]' }}
                    </td>
                </tr>
            </table>

            <table class="meta-strip">
                <tr>
                    <th>SALESPERSON</th>
                    <th>P.O. NUMBER</th>
                    <th>REQUISITIONER</th>
                    <th>SHIPPED VIA</th>
                    <th>F.O.B. POINT</th>
                    <th>TERMS</th>
                </tr>
                <tr>
                    <td>{{ $invoice->user?->name ?? '-' }}</td>
                    <td>{{ $invoice->po_number ?: '-' }}</td>
                    <td>{{ $invoice->reference_no ?: '-' }}</td>
                    <td>{{ $invoice->shipping_via ?? '-' }}</td>
                    <td>{{ $invoice->fob_point ?? '-' }}</td>
                    <td>{{ $invoice->payment_terms ?: '-' }}</td>
                </tr>
            </table>

            <table class="items">
                <colgroup>
                    <col style="width:16%;">
                    <col style="width:58%;">
                    <col style="width:14%;">
                    <col style="width:12%;">
                </colgroup>
                <tr>
                    <th>QUANTITY</th>
                    <th>DESCRIPTION</th>
                    <th>UNIT PRICE</th>
                    <th>TOTAL</th>
                </tr>
                @for ($i = 0; $i < $lineCount; $i++)
                    @php
                        $item = $invoice->items[$i] ?? null;
                    @endphp
                    <tr>
                        <td class="num">{{ $item ? number_format((float) $item->qty_billed, 2) : '' }}</td>
                        <td>{{ $item?->name ?? '' }}</td>
                        <td class="num">{{ $item ? $currencySymbol.number_format((float) $item->rate, 2) : '' }}</td>
                        <td class="num">{{ $item ? $currencySymbol.number_format((float) $item->amount, 2) : '' }}</td>
                    </tr>
                @endfor
            </table>

            <table class="summary">
                <colgroup>
                    <col style="width:60%;">
                    <col style="width:24%;">
                    <col style="width:16%;">
                </colgroup>
                <tr>
                    <td class="notes-title">Special Notes and Terms:</td>
                    <td class="label">SUBTOTAL</td>
                    <td class="value">{{ $currencySymbol }}{{ number_format((float) $invoice->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="notes-body" rowspan="3">{{ $notesCombined !== '' ? $notesCombined : 'Thank you for your business.' }}</td>
                    <td class="label">SALES TAX</td>
                    <td class="value">{{ $currencySymbol }}{{ number_format($salesTax, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">SHIPPING &amp; HANDLING</td>
                    <td class="value">{{ $currencySymbol }}{{ number_format((float) $invoice->round_off, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td class="label">TOTAL DUE</td>
                    <td class="value">{{ $currencySymbol }}{{ number_format((float) $invoice->grand_total, 2) }}</td>
                </tr>
            </table>

            <p class="thanks">Thank you for your business!</p>
        </div>
    </body>
</html>
