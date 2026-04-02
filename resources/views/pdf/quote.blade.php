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
            .totals td { border-top: 1px solid #cbd5f5; }
            .totals tr:last-child td { border-top: 2px solid #0f172a; }
            .grand-total { background: #0f172a; color: #fff; font-size: 14px; }
            .details { margin-top: 24px; display: flex; gap: 24px; }
            .details > div { flex: 1; }
            .highlight { font-weight: 600; }
        </style>
    </head>
    <body>
        @php
            $currencySymbol = config('invoice.currency_symbol') ?? "\u{20B9}";
            $currencyLabel = $quote->currency ?? 'INR';
        @endphp
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
            <div style="margin-bottom: 12px;">
                <p class="section-title">Quote details</p>
                <p>Currency: {{ $currencyLabel }}</p>
                <p>Salesperson: {{ $quote->salesperson ?? '—' }}</p>
                <p>Reference: {{ $quote->reference_no ?? '—' }}</p>
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
                        <td class="text-right">{{ $currencySymbol }}{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tbody>
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($quote->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Discount ({{ ucfirst($quote->discount_type) }} {{ $quote->discount_type === 'percent' ? number_format($quote->discount_value, 2).'%' : $currencySymbol.number_format($quote->discount_value, 2) }})</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($quote->discount_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Taxable amount</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($quote->subtotal - $quote->discount_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>CGST</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($quote->cgst, 2) }}</td>
                </tr>
                <tr>
                    <td>SGST</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($quote->sgst, 2) }}</td>
                </tr>
                <tr>
                    <td>IGST</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($quote->igst, 2) }}</td>
                </tr>
                <tr>
                    <td>Round off</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($quote->round_off, 2) }}</td>
                </tr>
                <tr class="grand-total">
                    <td>Grand total</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($quote->grand_total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="details">
            <div>
                <p class="section-title">Payment terms</p>
                <p>{{ $quote->payment_terms ?? 'No payment terms specified.' }}</p>
            </div>
            <div>
                <p class="section-title">Terms & Conditions</p>
                <p>{{ $quote->terms_conditions ?? 'No additional terms.' }}</p>
            </div>
        </div>

        @if($quote->notes)
            <div style="margin-top: 24px;">
                <p class="section-title">Notes</p>
                <p>{{ $quote->notes }}</p>
            </div>
        @endif
    </body>
</html>
