<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Quote {{ $quote->quote_number }}</title>
        <style>
            body { font-family: 'Nunito', sans-serif; color: #1f2a37; }
            .header { display: flex; justify-content: space-between; margin-bottom: 24px; }
            .section-title { font-size: 12px; letter-spacing: 0.3em; text-transform: uppercase; color: #6b7280; }
            table { width: 100%; border-collapse: collapse; margin-top: 16px; }
            th, td { padding: 8px; text-align: left; font-size: 12px; }
            th { background: #0f172a; color: #fff; font-size: 12px; }
            tbody tr:nth-child(odd) { background: #f8fafc; }
            .totals td { border-top: 2px solid #cbd5f5; }
        </style>
    </head>
    <body>
        <div class="header">
            <div>
                <p class="section-title">Business</p>
                <p><strong>{{ config('invoice.business_name') }}</strong></p>
                <p>GSTIN: {{ config('invoice.gstin') }}</p>
            </div>
            <div>
                <p class="section-title">Quote</p>
                <p><strong>{{ $quote->quote_number }}</strong></p>
                <p>Issue: {{ $quote->issue_date->format('M j, Y') }}</p>
                <p>Valid until: {{ $quote->validity_date->format('M j, Y') }}</p>
            </div>
        </div>

        <div class="grid">
            <div style="margin-bottom: 12px;">
                <p class="section-title">Client</p>
                <p><strong>{{ $quote->client->name }}</strong></p>
                <p>{{ $quote->client->email }}</p>
                <p>{{ $quote->client->phone ?? '' }}</p>
                <p>{{ $quote->client->address }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>GST %</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quote->items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ number_format($item->rate, 2) }}</td>
                        <td>{{ number_format($item->gst_percent, 2) }}</td>
                        <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tbody>
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">{{ number_format($quote->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>CGST</td>
                    <td class="text-right">{{ number_format($quote->cgst, 2) }}</td>
                </tr>
                <tr>
                    <td>SGST</td>
                    <td class="text-right">{{ number_format($quote->sgst, 2) }}</td>
                </tr>
                <tr>
                    <td>IGST</td>
                    <td class="text-right">{{ number_format($quote->igst, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td class="text-right"><strong>{{ number_format($quote->total, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        @if($quote->notes)
            <div style="margin-top: 24px;">
                <p class="section-title">Notes</p>
                <p>{{ $quote->notes }}</p>
            </div>
        @endif
    </body>
</html>
