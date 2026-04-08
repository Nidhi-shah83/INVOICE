<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Invoice {{ $invoice->invoice_number }}</title>
        <style>
            @page { margin: 0; }
            body { margin: 0; background: #f4f5f7; font-family: 'DejaVu Sans', sans-serif; }
            .page { width: 210mm; min-height: 297mm; margin: 0; background: #ffffff; padding: 0; }
            table { margin: 0; }
            table { width: 100%; border-collapse: collapse; }
            .header-table { background: #1e2a3a; color: #ffffff; border-radius: 8px; }
            .header-table td { padding: 16px; vertical-align: top; }
            .header-label { letter-spacing: 4px; font-size: 10px; text-transform: uppercase; margin: 0 0 4px; }
            .header-title { margin: 0; font-size: 28px; font-weight: 700; }
            .header-client { text-align: right; }
            .header-client-name { margin: 0; font-size: 18px; font-weight: 600; }
            .header-client-email { margin: 4px 0 0; font-size: 12px; }
            .info-card { margin-top: 12px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 8px; }
            .info-card td { padding: 16px; vertical-align: top; width: 50%; }
            .card-title { margin: 0 0 8px; letter-spacing: 3px; text-transform: uppercase; font-size: 10px; color: #6b7280; }
            .text-bold { margin: 4px 0; font-weight: 600; font-size: 13px; }
            .text-muted { margin: 2px 0; font-size: 11px; color: #3b4361; }
            .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #16a34a; color: #ffffff; font-size: 10px; margin-top: 6px; }
            .triple-card { margin-top: 12px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 8px; }
            .triple-card td { padding: 16px; vertical-align: top; width: 33.333%; }
            .triple-card td:not(:first-child) { border-left: 1px solid #e5e7eb; }
            .totals-label { margin: 4px 0; font-size: 11px; color: #374151; }
            .totals-big { margin: 8px 0 0; font-size: 20px; font-weight: 700; color: #0f172a; }
            .totals-warning { color: #dc2626; font-weight: 600; }
            .items-card { margin-top: 12px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 8px; }
            .items-table th { background: #1e2a3a; color: #ffffff; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; padding: 10px; }
            .items-table td { font-size: 11px; padding: 10px; border-bottom: 1px solid #e5e7eb; }
            .items-table td.amount { text-align: right; }
            .items-table tr:nth-child(even) { background: #f8fafc; }
            .breakdown { margin-top: 10px; width: 100%; }
            .breakdown-table { width: 48%; margin-left: auto; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; }
            .breakdown-table td { padding: 10px 12px; font-size: 11px; }
            .breakdown-table tr:nth-child(odd) { background: #f8fafc; }
            .meta-card { margin-top: 14px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 8px; }
            .meta-card td { padding: 16px; vertical-align: top; width: 33.333%; }
            .meta-card td:not(:first-child) { border-left: 1px solid #e5e7eb; }
            .meta-list { list-style: none; padding: 0; margin: 6px 0 0; font-size: 11px; }
            .meta-list li { margin-bottom: 4px; }
            .label-pill { display: block; font-size: 9px; letter-spacing: 3px; text-transform: uppercase; color: #6b7280; margin-bottom: 6px; }
            .signature-block { margin-top: 20px; padding-top: 16px; font-size: 11px; color: #0f172a; text-align: right;  margin-right: 16px; }
        </style>
    </head>
    <body>
        <div class="page">
            <table class="header-table">
                <tr>
                    <td>
                        <p class="header-label">INVOICE</p>
                        <h1 class="header-title">{{ $invoice->invoice_number }}</h1>
                    </td>
                    <td class="header-client">
                        <p class="header-client-name">{{ $invoice->client->name ?? 'Client' }}</p>
                        <p class="header-client-email">{{ $invoice->client->email ?? '—' }}</p>
                    </td>
                </tr>
            </table>

            <table class="info-card">
                <tr>
                    <td>
                        <p class="card-title">From</p>
                        <p class="text-bold">{{ setting('business_name', config('app.company_name', config('app.name', 'Invoice App'))) }}</p>
                        <p class="text-muted">{{ setting('address', '—') }}</p>
                        <p class="text-muted">GSTIN: {{ setting('gstin', '—') }}</p>
                        <p class="text-muted">{{ setting('email', '—') }}</p>
                        <p class="text-muted">{{ setting('phone', '—') }}</p>
                    </td>
                    <td>
                        <p class="card-title">Bill To</p>
                        <p class="text-bold">{{ $invoice->client->name ?? 'Client' }}</p>
                        <p class="text-muted">{{ $invoice->client->address ?? '—' }}</p>
                        <p class="text-muted">{{ $invoice->client->email ?? '—' }}</p>
                        <p class="text-muted">{{ $invoice->client->phone ?? '—' }}</p>
                        @if(! empty($invoice->client->gst_type))
                            <span class="badge">{{ $invoice->client->gst_type }}</span>
                        @endif
                    </td>
                </tr>
            </table>

            <table class="triple-card">
                <tr>
                    <td>
                        <p class="card-title">Dates</p>
                        <p class="text-muted"><strong>Issue:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('M d, Y') }}</p>
                        <p class="text-muted"><strong>Due:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</p>
                    </td>
                    <td>
                        <p class="card-title">Payment Info</p>
                        <p class="text-muted"><strong>Currency:</strong> {{ $invoice->currency ?? 'INR' }}</p>
                        <p class="text-muted"><strong>Terms:</strong> {{ $invoice->payment_terms ?? '—' }}</p>
                    </td>
                </tr>
            </table>

            <table class="items-card">
                <tr>
                    <td>
                        <table class="items-table">
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
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ number_format((float) ($item->quantity ?? 0), 2) }}</td>
                                        <td>{{ number_format((float) ($item->rate ?? 0), 2) }}</td>
                                        <td>{{ number_format((float) ($item->gst_percentage ?? 0), 2) }}</td>
                                        <td class="amount">{{ number_format((float) ($item->amount ?? 0), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="breakdown">
                <table class="breakdown-table">
                    <tr>
                        <td>Subtotal</td>
                        <td style="text-align: right;">{{ number_format((float) ($invoice->subtotal ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td>CGST</td>
                        <td style="text-align: right;">{{ number_format((float) ($invoice->cgst ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td>SGST</td>
                        <td style="text-align: right;">{{ number_format((float) ($invoice->sgst ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td>IGST</td>
                        <td style="text-align: right;">{{ number_format((float) ($invoice->igst ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td style="text-align: right; font-weight: 600;">{{ number_format((float) ($invoice->total ?? 0), 2) }}</td>
                    </tr>
                </table>
            </div>

            <table class="meta-card">
                <tr>
                    <td>
                        <p class="label-pill">Reference &amp; Meta</p>
                        <ul class="meta-list">
                            <li><strong>Invoice Type:</strong> {{ $invoice->invoice_type ?? '—' }}</li>
                            <li><strong>PO #:</strong> {{ $invoice->po_number ?? '—' }}</li>
                            <li><strong>Ref #:</strong> {{ $invoice->ref_number ?? '—' }}</li>
                        </ul>
                    </td>
                    <td>
                        <p class="label-pill">Bank Details</p>
                        <ul class="meta-list">
                            <li><strong>Bank:</strong> {{ $invoice->bank_name ?? '—' }}</li>
                            <li><strong>Account:</strong> {{ $invoice->bank_account ?? '—' }}</li>
                            <li><strong>IFSC:</strong> {{ $invoice->ifsc_code ?? '—' }}</li>
                        </ul>
                    </td>
                    <td>
                        <p class="label-pill">Notes</p>
                        <p>{{ $invoice->notes ?: '—' }}</p>
                    </td>
                </tr>
            </table>
            <div class="signature-block">
                <strong>{{ setting('signature_name', setting('business_name', config('app.name', 'Invoice App'))) }}</strong><br>
                <span>{{ setting('signature_title', 'Authorized Signatory') }}</span>
            </div>
        </div>
    </body>
</html>
