<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: 'Inter', sans-serif; color: #0f172a; }
            .header { display:flex; justify-content:space-between; margin-bottom:24px; }
            .badge { padding:6px 12px; border-radius:999px; font-size:12px; background:#dc2626; color:#fff; }
            .table { width:100%; border-collapse:collapse; margin-top:16px; }
            .table th, .table td { border-bottom:1px solid #e2e8f0; padding:8px; }
            .totals { margin-top:16px; display:flex; justify-content:flex-end; gap:16px; }
            .footer { margin-top:32px; font-size:12px; color:#475569; }
        </style>
    </head>
    <body>
        <div class="header">
            <div>
                <p style="margin:0;">{{ config('invoice.business_name') }}</p>
                <p style="margin:2px 0;">GSTIN: {{ config('invoice.gstin') }}</p>
            </div>
            <div>
                <p style="margin:0;">Invoice #{{ $invoice->invoice_number }}</p>
                <p style="margin:2px 0;">Issue: {{ $invoice->issue_date?->format('F d, Y') }}</p>
                <p style="margin:2px 0;">Due: {{ $invoice->due_date?->format('F d, Y') }}</p>
            </div>
        </div>
        @if($invoice->due_date && $invoice->due_date->isPast())
            <div class="badge">Overdue {{ now()->diffInDays($invoice->due_date) }} days</div>
        @endif

        <p><strong>Bill To:</strong> {{ $invoice->client->name }} · {{ $invoice->client->state }}</p>
        <table class="table">
            <thead>
                <tr>
                    <th align="left">Item</th>
                    <th align="right">Qty</th>
                    <th align="right">Rate</th>
                    <th align="right">GST%</th>
                    <th align="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td align="right">{{ number_format($item->qty_billed, 2) }}</td>
                        <td align="right">₹{{ number_format($item->rate, 2) }}</td>
                        <td align="right">{{ number_format($item->gst_percent, 2) }}%</td>
                        <td align="right">₹{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="totals">
            <div>
                <p style="margin:2px 0;">Subtotal: ₹{{ number_format($invoice->subtotal, 2) }}</p>
                <p style="margin:2px 0;">CGST: ₹{{ number_format($invoice->cgst, 2) }}</p>
                <p style="margin:2px 0;">SGST: ₹{{ number_format($invoice->sgst, 2) }}</p>
                <p style="margin:2px 0;">IGST: ₹{{ number_format($invoice->igst, 2) }}</p>
                <p style="margin:2px 0; font-weight:bold;">Total: ₹{{ number_format($invoice->total, 2) }}</p>
            </div>
        </div>
        <p class="footer">Payment Link: {{ $invoice->payment_link ?? 'N/A' }} · GSTIN: {{ config('invoice.gstin') }}</p>
    </body>
</html>
