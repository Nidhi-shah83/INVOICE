<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: 'Inter', sans-serif; color: #0f172a; margin:0; padding:0; background:#f8fafc; }
            .wrapper { padding: 36px; }
            .header, .section { margin-bottom: 24px; }
            .header-sheet { display:flex; justify-content:space-between; }
            .badge { padding:4px 14px; border-radius:999px; font-size:11px; text-transform:uppercase; }
            .bg-red { background:#fee2e2; color:#991b1b; }
            .bg-emerald { background:#d1fae5; color:#064e3b; }
            .table { width:100%; border-collapse:collapse; }
            .table th, .table td { border-bottom:1px solid #e2e8f0; padding:10px; }
            .table thead { background:#0f172a; color:#fff; font-size:12px; }
            .grid-2 { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px; }
            .card { background:#fff; border-radius:20px; border:1px solid #e2e8f0; padding:16px; }
            .totals { display:flex; flex-direction:column; gap:6px; margin-top:12px; font-size:13px; }
            .totals strong { font-weight:600; }
            .footer { margin-top:32px; font-size:11px; color:#475569; display:flex; justify-content:space-between; }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <div class="header">
                <div class="header-sheet">
                    <div>
                        <p style="margin:0; font-size:18px;">{{ config('invoice.business_name') }}</p>
                        <p style="margin:2px 0; font-size:12px;">GSTIN: {{ config('invoice.gstin') ?: '—' }}</p>
                        <p style="margin:2px 0; font-size:12px;">State: {{ config('invoice.state') }}</p>
                    </div>
                    <div style="text-align:right;">
                        <p style="margin:0; font-size:14px;">Invoice #{{ $invoice->invoice_number }}</p>
                        <p style="margin:2px 0; font-size:12px;">Type: {{ ucfirst($invoice->invoice_type) }}</p>
                        <p style="margin:2px 0; font-size:12px;">Issued: {{ $invoice->issue_date?->format('F d, Y') }}</p>
                        <p style="margin:2px 0; font-size:12px;">Due: {{ $invoice->due_date?->format('F d, Y') }}</p>
                    </div>
                </div>
                <div class="mt-3" style="display:flex; gap:8px; flex-wrap:wrap;">
                    <div class="badge bg-emerald">Payment {{ $invoice->payment_status }}</div>
                    @if($invoice->is_overdue)
                        <div class="badge bg-red">Overdue</div>
                    @endif
                </div>
            </div>

            <div class="section grid-2">
                <div class="card">
                    <p style="margin-bottom:8px; font-size:12px; text-transform:uppercase; letter-spacing:2px;">Bill to</p>
                    <p style="margin:0; font-weight:600;">{{ $invoice->client->name }}</p>
                    <p style="margin:4px 0 0; font-size:12px;">{{ $invoice->client->email }}</p>
                    <p style="margin:4px 0 0; font-size:12px;">{{ $invoice->client->address }}</p>
                    <p style="margin:4px 0 0; font-size:12px;">GST Type: {{ strtoupper($invoice->client->gst_type ?? '—') }}</p>
                </div>
                <div class="card">
                    <p style="margin-bottom:8px; font-size:12px; text-transform:uppercase; letter-spacing:2px;">Payment details</p>
                    <p style="margin:0; font-size:12px;">Payment terms: {{ $invoice->payment_terms ?? 'As agreed' }}</p>
                    <p style="margin:4px 0 0; font-size:12px;">Reference #: {{ $invoice->reference_no ?: '—' }}</p>
                    <p style="margin:4px 0 0; font-size:12px;">PO #: {{ $invoice->po_number ?: '—' }}</p>
                    <p style="margin:4px 0 0; font-size:12px;">Currency: {{ $invoice->currency }}</p>
                </div>
            </div>

            <div class="section card">
                <h3 style="margin:0 0 12px; font-size:14px;">Items</h3>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th align="left">Item</th>
                                <th align="right">Qty</th>
                                <th align="right">Rate</th>
                                <th align="right">GST %</th>
                                <th align="right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td align="right">{{ number_format($item->qty_billed, 2) }}</td>
                                    <td align="right">{{ config('invoice.currency_symbol', '?') }}{{ number_format($item->rate, 2) }}</td>
                                    <td align="right">{{ number_format($item->gst_percent, 2) }}%</td>
                                    <td align="right">{{ config('invoice.currency_symbol', '?') }}{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="totals">
                    <div class="flex items-center justify-between">
                        <span>Subtotal</span>
                        <strong>{{ config('invoice.currency_symbol', '?') }}{{ number_format($invoice->subtotal, 2) }}</strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Discount</span>
                        <strong>
                            {{ $invoice->discount_type === 'percent' ? number_format($invoice->discount_value, 2).'%' : config('invoice.currency_symbol', '?').number_format($invoice->discount_value, 2) }}
                        </strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>GST total</span>
                        <strong>
                            {{ config('invoice.currency_symbol', '?') }}{{ number_format($invoice->cgst + $invoice->sgst + $invoice->igst, 2) }}
                        </strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Round off</span>
                        <strong>{{ config('invoice.currency_symbol', '?') }}{{ number_format($invoice->round_off, 2) }}</strong>
                    </div>
                    <div class="flex items-center justify-between" style="font-size:16px;">
                        <span>Grand total</span>
                        <strong>{{ $invoice->formatted_grand_total }}</strong>
                    </div>
                </div>
            </div>

            <div class="section grid-2">
                <div class="card">
                    <p style="margin:0 0 8px; font-size:12px; text-transform:uppercase; letter-spacing:2px;">Payment summary</p>
                    <p style="margin:0;">Amount paid: {{ config('invoice.currency_symbol', '?') }}{{ number_format($invoice->amount_paid, 2) }}</p>
                    <p style="margin:4px 0 0;">Amount due: <strong>{{ config('invoice.currency_symbol', '?') }}{{ number_format($invoice->amount_due, 2) }}</strong></p>
                    <p style="margin:4px 0 0;">Due date: {{ $invoice->due_date?->format('F d, Y') }}</p>
                </div>
                <div class="card">
                    <p style="margin:0 0 8px; font-size:12px; text-transform:uppercase; letter-spacing:2px;">Bank details</p>
                    <p style="margin:0;">{{ $invoice->bank_name ?: 'Bank details not provided' }}</p>
                    <p style="margin:4px 0 0;">A/c #: {{ $invoice->account_number ?: '—' }}</p>
                    <p style="margin:4px 0 0;">IFSC: {{ $invoice->ifsc_code ?: '—' }}</p>
                    <p style="margin:4px 0 0;">UPI ID: {{ $invoice->upi_id ?: '—' }}</p>
                </div>
            </div>

            <div class="section card">
                <p style="margin:0 0 10px; font-size:12px; text-transform:uppercase; letter-spacing:2px;">Terms & conditions</p>
                <p style="margin:0; font-size:12px;">{{ $invoice->terms_conditions ?? 'Thank you for doing business with us.' }}</p>
            </div>

            <div class="footer">
                <div>
                    @if($invoice->payment_link)
                        <p style="margin:0; font-size:12px;">Pay online: {{ $invoice->payment_link }}</p>
                    @endif
                    <p style="margin:0; font-size:12px;">Authorized signature</p>
                </div>
                <div>
                    <p style="margin:0; font-size:12px;">{{ config('invoice.business_name') }}</p>
                    <p style="margin:0; font-size:12px;">GSTIN: {{ config('invoice.gstin') ?: '—' }}</p>
                </div>
            </div>
        </div>
    </body>
</html>
