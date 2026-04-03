<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Quote {{ $quote->quote_number }}</title>
    </head>
    <body style="font-family: 'Inter', sans-serif; background:#f5f5f0; padding: 32px;">
        @php
            $symbol = config('invoice.currency_symbol', '₹');
        @endphp
        <div style="max-width: 640px; margin: auto; background:#fff; padding: 32px; border-radius: 16px; box-shadow: 0 12px 40px rgba(15,23,42,.15);">
            <h1 style="margin-top:0; font-size: 24px; color:#0f172a;">Quote {{ $quote->quote_number }}</h1>
            <p style="color:#334155;">Hi {{ $quote->client->name }},</p>
            <p style="color:#475569;">
                Please review the attached quote from {{ config('invoice.business_name') }}. You can accept it online before {{ $quote->validity_date->format('M j, Y') }}.
            </p>
            <a
                href="{{ $acceptUrl }}"
                style="display:inline-flex; align-items:center; justify-content:center; gap:.5rem; background:#059669; color:#fff; padding:14px 28px; border-radius:999px; text-decoration:none; font-weight:600; margin-top:16px;"
            >
                Accept This Quote
            </a>
            <div style="margin-top:32px; color:#1f2937; font-size:14px; line-height:1.6;">
                <p><strong>Validity date:</strong> {{ $quote->validity_date->format('M j, Y') }}</p>
                <p><strong>Payment terms:</strong> {{ $quote->payment_terms ?? 'Payment due upon acceptance.' }}</p>
                @if($quote->reference_no)
                    <p><strong>Reference:</strong> {{ $quote->reference_no }}</p>
                @endif
            </div>

            <table style="width:100%; margin-top:24px; border-collapse:collapse; font-size:14px;">
                <thead>
                    <tr style="background:#0f172a; color:#fff;">
                        <th style="padding:8px; text-align:left;">Item</th>
                        <th style="padding:8px; text-align:left;">Qty</th>
                        <th style="padding:8px; text-align:left;">Rate</th>
                        <th style="padding:8px; text-align:left;">GST%</th>
                        <th style="padding:8px; text-align:right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quote->items as $item)
                        <tr style="background:#f8fafc;">
                            <td style="padding:8px;">{{ $item->name }}</td>
                            <td style="padding:8px;">{{ $item->qty }}</td>
                            <td style="padding:8px;">{{ number_format($item->rate, 2) }}</td>
                            <td style="padding:8px;">{{ number_format($item->gst_percent, 2) }}</td>
                            <td style="padding:8px; text-align:right;">{{ $symbol }}{{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:24px; border-top:1px solid #e2e8f0; padding-top:16px; color:#1f2937;">
                <p style="margin:0; display:flex; justify-content:space-between;"><span>Subtotal</span><span>{{ $symbol }}{{ number_format($quote->subtotal, 2) }}</span></p>
                <p style="margin:0; display:flex; justify-content:space-between;"><span>Discount</span><span>{{ $symbol }}{{ number_format($quote->discount_amount, 2) }}</span></p>
                <p style="margin:0; display:flex; justify-content:space-between;"><span>Taxable amount</span><span>{{ $symbol }}{{ number_format($quote->subtotal - $quote->discount_amount, 2) }}</span></p>
                <p style="margin:0; display:flex; justify-content:space-between;"><span>CGST</span><span>{{ $symbol }}{{ number_format($quote->cgst, 2) }}</span></p>
                <p style="margin:0; display:flex; justify-content:space-between;"><span>SGST</span><span>{{ $symbol }}{{ number_format($quote->sgst, 2) }}</span></p>
                <p style="margin:0; display:flex; justify-content:space-between;"><span>IGST</span><span>{{ $symbol }}{{ number_format($quote->igst, 2) }}</span></p>
                <p style="margin:0; display:flex; justify-content:space-between;"><span>Round off</span><span>{{ $symbol }}{{ number_format($quote->round_off, 2) }}</span></p>
                <p style="margin:8px 0 0 0; display:flex; justify-content:space-between; font-size:18px; font-weight:600;"><span>Grand total</span><span>{{ $symbol }}{{ number_format($quote->grand_total, 2) }}</span></p>
            </div>
            @if($quote->terms_conditions)
                <p style="margin-top:24px; color:#475569; font-size:13px;">{{ $quote->terms_conditions }}</p>
            @endif
            <p style="color:#94a3b8; font-size:12px; margin-top:24px;">If you have questions, reply to this email and we’ll help right away.</p>
        </div>
    </body>
</html>
