<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Invoice {{ $invoice->invoice_number }}</title>
        <style>
            @page {
                size: A4 portrait;
                margin: 0 !important;
            }

            html,
            body {
                margin: 0;
                padding: 0;
            }

            body {
                background: #f3f4f6;
                font-family: 'DejaVu Sans', sans-serif;
                color: #111827;
            }

            * {
                box-sizing: border-box;
            }

            .page {
                width: 100%;
                min-height: 297mm;
                padding: 0;
                margin: 0;
            }

            table {
                border-collapse: collapse;
                width: 100%;
                margin: 0;
            }

            .header-table {
                background: #1e2a3a;
                padding: 12px 10px;
                color: #ffffff;
            }

            .header-table td {
                vertical-align: top;
                padding: 0;
            }

            .header-title {
                font-size: 28px;
                margin: 0;
                font-weight: 700;
            }

            .header-label {
                font-size: 10px;
                letter-spacing: 3px;
                text-transform: uppercase;
                color: rgba(255, 255, 255, 0.75);
                margin-bottom: 6px;
            }

            .meta-table {
                width: 100%;
            }

            .meta-label {
                font-size: 9px;
                letter-spacing: 2px;
                text-transform: uppercase;
                color: #8a9bb0;
                padding-right: 8px;
                width: 60%;
            }

            .meta-value {
                font-size: 11px;
                letter-spacing: 1px;
                font-weight: 600;
                text-transform: uppercase;
            }

            .contact-row {
                margin: 16px 0;
                width: 100%;
            }

            .contact-box {
                border: 1px solid #e3e8ef;
                border-radius: 10px;
                padding: 16px;
                background: #ffffff;
                box-sizing: border-box;
            }

            .contact-label {
                font-size: 8px;
                letter-spacing: 2px;
                text-transform: uppercase;
                color: #7a8fa6;
                margin: 0 0 6px;
            }

            .contact-title {
                font-size: 12px;
                font-weight: 700;
                margin: 0 0 6px;
                color: #1e2a3a;
            }

            .contact-text {
                margin: 2px 0;
                font-size: 10px;
                color: #4b5563;
                line-height: 1.6;
            }

            .items-table {
                width: 100%;
                border: 1px solid #e3e8ef;
                background: #ffffff;
                table-layout: fixed;
                font-size: 10px;
            }

            .items-table thead th {
                background: #1e2a3a;
                color: #ffffff;
                text-transform: uppercase;
                letter-spacing: 1px;
                font-size: 9px;
                padding: 10px;
                border: none;
                text-align: left;
            }

            .items-table tbody td {
                padding: 10px;
                border-bottom: 1px solid #e8edf4;
                color: #1f2937;
                word-break: break-word;
            }

            .items-table tbody tr:last-child td {
                border-bottom: none;
            }

            .items-table td.amount,
            .items-table th.amount {
                text-align: right;
                white-space: nowrap;
            }

            .summary-panel-wrapper {
                text-align: right;
            }

            .summary-panel {
                background: #ffffff;
                border: 1px solid #e3e8ef;
                border-radius: 10px;
                padding: 12px 14px;
                width: 100%;
                max-width: 320px;
                display: inline-block;
            }

            .fin-table td {
                padding: 6px 4px;
            }

            .fin-label {
                width: 60%;
                font-size: 10px;
                color: #374151;
            }

            .fin-value {
                width: 40%;
                font-size: 10px;
                font-weight: 600;
                text-align: right;
                color: #374151;
                white-space: nowrap;
            }

            .taxable-row .fin-label,
            .taxable-row .fin-value {
                font-weight: 700;
                color: #1e2a3a;
            }

            .grand-total .fin-label,
            .grand-total .fin-value {
                font-size: 12px;
                font-weight: 700;
                color: #1e2a3a;
                text-transform: uppercase;
            }

            .paid-row .fin-value {
                color: #1a7a4a;
            }

            .balance-row .fin-value {
                color: #c0392b;
                font-weight: 700;
            }

            .fin-divider td {
                padding: 4px 0;
            }

            .hr-dashed {
                border: none;
                border-top: 1px dashed #d0d7e3;
                margin: 6px 0;
            }

            .footer-table {
                margin-top: 18px;
            }

            .footer-table td {
                vertical-align: top;
                padding-top: 12px;
            }

            .footer-label {
                font-size: 8px;
                letter-spacing: 2px;
                text-transform: uppercase;
                color: #7a8fa6;
                margin: 0 0 6px;
            }

            .footer-text {
                font-size: 10px;
                line-height: 1.6;
                color: #4b5563;
                margin: 0;
            }

            .signature-table {
                margin-top: 18px;
                width: 100%;
            }

            .signature-cell {
                text-align: right;
            }

            .signature-label {
                font-size: 8px;
                letter-spacing: 2px;
                text-transform: uppercase;
                color: #7a8fa6;
                margin: 0 0 6px;
                white-space: nowrap;
            }

            .signature-box {
                width: 100%;
                height: 54px;
                border: 1px dashed #b0bac8;
                border-radius: 6px;
                margin-bottom: 8px;
                box-sizing: border-box;
            }

            .signature-name {
                margin: 0;
                font-size: 10px;
                font-weight: 700;
                color: #1e2a3a;
                white-space: nowrap;
            }

            .hr-solid {
                border: none;
                border-top: 1px solid #d0d7e3;
                margin: 6px 0;
            }

            .stamp {
                margin-top: 16px;
                border-top: 1px solid #e3e8ef;
                padding-top: 10px;
                text-align: center;
                font-size: 9px;
                letter-spacing: 3px;
                text-transform: uppercase;
                color: #7a8fa6;
            }
        </style>
    </head>
    <body>
        @php
            $currencySymbol = config('invoice.currency_symbol', '₹');
            $formatMoney = function ($value) {
                return '&#8377;'.number_format((float) $value, 2);
            };
            $invoiceDate = $invoice->issue_date?->format('d M, Y') ?? '-';
            $dueDate = $invoice->due_date?->format('d M, Y') ?? '-';
            $company = [
                'name' => $settingsService->get('business_name', config('company.name', '—')),
                'address' => $settingsService->get('address', config('company.address', '—')),
                'gstin' => $settingsService->get('gstin', config('company.gstin', '—')),
            ];
            $client = $invoice->client;
            $logo = null;
            if (! empty($settingsService->get('logo'))) {
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
            $shipping = [
                'name' => data_get($invoice, 'ship_to_name') ?: $client?->name ?: '—',
                'address' => data_get($invoice, 'ship_to_address') ?: $client?->address ?: '—',
                'gstin' => data_get($invoice, 'ship_to_gstin') ?: $client?->gstin ?: '—',
            ];
            $summary = [
                'subtotal' => (float) ($invoice->subtotal ?? 0),
                'discount' => (float) ($invoice->discount_amount ?? 0),
                'taxable_amount' => max(0, (float) ($invoice->subtotal ?? 0) - (float) ($invoice->discount_amount ?? 0)),
                'cgst' => (float) ($invoice->cgst ?? 0),
                'sgst' => (float) ($invoice->sgst ?? 0),
                'igst' => (float) ($invoice->igst ?? 0),
                'tds' => (float) ($invoice->tds ?? 0),
                'round_off' => (float) ($invoice->round_off ?? 0),
                'grand_total' => (float) ($invoice->grand_total ?? $invoice->total ?? 0),
                'amount_paid' => (float) ($invoice->amount_paid ?? 0),
                'balance_due' => (float) ($invoice->amount_due ?? max(0, ($invoice->grand_total ?? $invoice->total ?? 0) - ($invoice->amount_paid ?? 0))),
            ];
            $bankDetails = [
                'name' => $invoice->bank_name ?: $settingsService->get('bank_name', '—'),
                'account' => $invoice->account_number ?: $settingsService->get('account_number', '—'),
                'ifsc' => $invoice->ifsc_code ?: $settingsService->get('ifsc_code', '—'),
                'branch' => $invoice->bank_branch ?? '—',
                'upi' => $settingsService->get('upi_id', null),
            ];
        @endphp

        <div class="page">
            <table class="header-table" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="60%">
                        @if(! empty($logo))
                            <img src="data:image/png;base64,{{ $logo }}" alt="Logo" style="max-height: 56px; margin-bottom: 12px; display: block;" />
                        @endif
                        <p class="header-label">Invoice</p>
                        <p class="header-title">{{ $invoice->invoice_number }}</p>
                    </td>
                    <td width="40%">
                        <table class="meta-table" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="meta-label">Status</td>
                                <td class="meta-value">{{ strtoupper($invoice->status ?? 'draft') }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Invoice Date</td>
                                <td class="meta-value">{{ strtoupper($invoiceDate) }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Due Date</td>
                                <td class="meta-value">{{ strtoupper($dueDate) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table class="contact-row" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="32%">
                        <div class="contact-box">
                            <p class="contact-label">From</p>
                            <p class="contact-title">{{ $company['name'] }}</p>
                            <p class="contact-text">{{ $company['address'] }}</p>
                            <p class="contact-text">GSTIN {{ $company['gstin'] }}</p>
                        </div>
                    </td>
                    <td width="2%"></td>
                    <td width="32%">
                        <div class="contact-box">
                            <p class="contact-label">Bill To</p>
                            <p class="contact-title">{{ $client?->name ?? '—' }}</p>
                            <p class="contact-text">{{ $client?->address ?? '—' }}</p>
                            <p class="contact-text">GSTIN {{ $client?->gstin ?? '—' }}</p>
                            <p class="contact-text">Email: {{ $client?->email ?? '—' }}</p>
                            <p class="contact-text">Phone: {{ $client?->phone ?? '—' }}</p>
                        </div>
                    </td>
                    <td width="2%"></td>
                    <td width="32%">
                        <div class="contact-box">
                            <p class="contact-label">Ship To</p>
                            <p class="contact-title">{{ $shipping['name'] }}</p>
                            <p class="contact-text">{{ $shipping['address'] }}</p>
                            <p class="contact-text">GSTIN {{ $shipping['gstin'] }}</p>
                        </div>
                    </td>
                </tr>
            </table>

            <table class="items-table" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th style="width:28%;">Item</th>
                        <th style="width:10%;">Qty</th>
                        <th style="width:15%;">Rate</th>
                        <th style="width:16%;">Discount %</th>
                        <th style="width:12%;">Tax %</th>
                        <th class="amount" style="width:20%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ number_format($item->qty_billed ?? $item->quantity ?? 0, 2) }}</td>
                            <td class="amount">{!! $formatMoney($item->rate ?? 0) !!}</td>
                            <td>{{ number_format($item->discount_percent ?? 0, 2) }}%</td>
                            <td>{{ number_format($item->gst_percent ?? 0, 2) }}%</td>
                            <td class="amount">{!! $formatMoney($item->amount ?? 0) !!}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No items added.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <table class="items-finance" cellpadding="0" cellspacing="0" style="width:100%; margin-top: 12px;">
                <tr>
                    <td width="55%" valign="top"></td>
                    <td width="2%"></td>
                    <td width="43%" valign="top" style="padding-left: 10px;">
                        <div class="summary-panel-wrapper">
                            <div class="summary-panel">
                                <table class="fin-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="fin-label">Subtotal</td>
                                    <td class="fin-value">{!! $formatMoney($summary['subtotal']) !!}</td>
                                </tr>
                                <tr>
                                    <td class="fin-label">Discount</td>
                                    <td class="fin-value">{!! $formatMoney($summary['discount']) !!}</td>
                                </tr>
                                <tr class="taxable-row">
                                    <td class="fin-label">Taxable Amount</td>
                                    <td class="fin-value">{!! $formatMoney($summary['taxable_amount']) !!}</td>
                                </tr>
                                <tr class="fin-divider">
                                    <td colspan="2"><hr class="hr-dashed"></td>
                                </tr>
                                <tr>
                                    <td class="fin-label">CGST</td>
                                    <td class="fin-value">{!! $formatMoney($summary['cgst']) !!}</td>
                                </tr>
                                <tr>
                                    <td class="fin-label">SGST</td>
                                    <td class="fin-value">{!! $formatMoney($summary['sgst']) !!}</td>
                                </tr>
                                <tr>
                                    <td class="fin-label">IGST</td>
                                    <td class="fin-value">{!! $formatMoney($summary['igst']) !!}</td>
                                </tr>
                                <tr class="fin-divider">
                                    <td colspan="2"><hr class="hr-dashed"></td>
                                </tr>
                                @if($summary['tds'] > 0)
                                    <tr>
                                        <td class="fin-label">TDS</td>
                                        <td class="fin-value">{!! $formatMoney($summary['tds']) !!}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="fin-label">Round Off</td>
                                    <td class="fin-value">{!! $formatMoney($summary['round_off']) !!}</td>
                                </tr>
                                <tr class="fin-divider">
                                    <td colspan="2"><hr class="hr-solid"></td>
                                </tr>
                                <tr class="grand-total">
                                    <td class="fin-label">Grand Total</td>
                                    <td class="fin-value">{!! $formatMoney($summary['grand_total']) !!}</td>
                                </tr>
                                <tr class="fin-divider">
                                    <td colspan="2"><hr class="hr-dashed"></td>
                                </tr>
                                <tr class="paid-row">
                                    <td class="fin-label">Amount Paid</td>
                                    <td class="fin-value">{!! $formatMoney($summary['amount_paid']) !!}</td>
                                </tr>
                                <tr class="balance-row">
                                    <td class="fin-label">Balance Due</td>
                                    <td class="fin-value">{!! $formatMoney($summary['balance_due']) !!}</td>
                                </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <table class="footer-table" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="45%">
                        <p class="footer-label">Notes</p>
                        <p class="footer-text">{{ $invoice->notes ?? '—' }}</p>
                    </td>
                    <td width="10%"></td>
                    <td width="45%">
                        <p class="footer-label">Terms &amp; Conditions</p>
                        <p class="footer-text">{{ $invoice->terms_conditions ?? '—' }}</p>
                    </td>
                </tr>
            </table>

            <table class="footer-table" cellpadding="0" cellspacing="0" style="margin-top: 22px;">
                <tr>
                    <td width="100%">
                        <div class="contact-box" style="border-color:#dfe4ec; padding:16px; background:#f8fafc;">
                            <p class="footer-label">Bank Details</p>
                            <p class="footer-text"><strong>Bank:</strong> {{ $bankDetails['name'] }}</p>
                            <p class="footer-text"><strong>Account:</strong> {{ $bankDetails['account'] }}</p>
                            <p class="footer-text"><strong>IFSC:</strong> {{ $bankDetails['ifsc'] }}</p>
                            <p class="footer-text"><strong>Branch:</strong> {{ $bankDetails['branch'] }}</p>
                            @if(! empty($bankDetails['upi']))
                                <p class="footer-text"><strong>UPI:</strong> {{ $bankDetails['upi'] }}</p>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>

            <table class="signature-table" cellpadding="0" cellspacing="0" style="width:100%;">
                <tr>
                    <td width="50%"></td>
                    <td width="50%" class="signature-cell" style="padding-left: 10px;">
                        <p class="signature-label">Digital Signature</p>
                        <div class="signature-box"></div>
                        <p class="signature-name">Authorized Signatory</p>
                    </td>
                </tr>
            </table>

            <div class="stamp">Original for Recipient</div>
        </div>
    </body>
</html>
