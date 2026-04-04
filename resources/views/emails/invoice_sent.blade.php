<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Invoice {{ $invoice->invoice_number }}</title>
    </head>
    <body style="font-family: 'Inter', sans-serif; background:#f5f5f0; padding: 32px;">
        <div style="max-width: 640px; margin:auto; background:#fff; padding:32px; border-radius:16px; box-shadow:0 12px 40px rgba(15,23,42,.15);">
            @php $currencySymbol = config('invoice.currency_symbol', '?'); @endphp
            <p style="margin:0 0 6px; font-size:14px; color:#94a3b8;">{{ $settingsService->get('business_name', config('invoice.business_name')) }}</p>
            <h1 style="margin:8px 0 16px; color:#0f172a;">Invoice {{ $invoice->invoice_number }}</h1>
            <p style="color:#475569;">Hi {{ $invoice->client->name }},</p>
            <p style="color:#475569;">
                Please find your invoice attached. The total due is <strong>{{ $invoice->formatted_grand_total }}</strong>. Pay securely before <strong>{{ $invoice->due_date?->format('F d, Y') }}</strong> to avoid a reminder.
            </p>
            <a href="{{ route('invoices.pay', $invoice->id) }}" style="display:inline-flex; align-items:center; justify-content:center; background:#059669; color:#fff; padding:14px 28px; border-radius:999px; text-decoration:none; font-weight:600; margin-top:16px;">
                Make Payment
            </a>

            <div style="margin-top:32px;">
                <table style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr>
                            <th style="text-align:left; padding:8px 4px; border-bottom:1px solid #e2e8f0;">Item</th>
                            <th style="text-align:right; padding:8px 4px; border-bottom:1px solid #e2e8f0;">Qty</th>
                            <th style="text-align:right; padding:8px 4px; border-bottom:1px solid #e2e8f0;">Rate</th>
                            <th style="text-align:right; padding:8px 4px; border-bottom:1px solid #e2e8f0;">GST %</th>
                            <th style="text-align:right; padding:8px 4px; border-bottom:1px solid #e2e8f0;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            <tr>
                                <td style="padding:8px 4px; border-bottom:1px solid #f1f5f9;">{{ $item->name }}</td>
                                <td style="padding:8px 4px; text-align:right; border-bottom:1px solid #f1f5f9;">{{ number_format($item->qty_billed, 2) }}</td>
                                <td style="padding:8px 4px; text-align:right; border-bottom:1px solid #f1f5f9;">{{ $currencySymbol }}{{ number_format($item->rate, 2) }}</td>
                                <td style="padding:8px 4px; text-align:right; border-bottom:1px solid #f1f5f9;">{{ number_format($item->gst_percent, 2) }}%</td>
                                <td style="padding:8px 4px; text-align:right; border-bottom:1px solid #f1f5f9;">{{ $currencySymbol }}{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:32px; color:#1f2937; font-size:14px; line-height:1.6;">
                <p><strong>Grand Total:</strong> {{ $invoice->formatted_grand_total }}</p>
                <p><strong>Amount Due:</strong> {{ $currencySymbol }}{{ number_format($invoice->amount_due, 2) }}</p>
                <p><strong>Amount Paid:</strong> {{ $currencySymbol }}{{ number_format($invoice->amount_paid, 2) }}</p>
                <p><strong>Due Date:</strong> {{ $invoice->due_date?->format('F d, Y') }}</p>
                <p><strong>Payment terms:</strong> {{ $invoice->payment_terms ?? 'As agreed' }}</p>
                <p><strong>Reference:</strong> {{ $invoice->reference_no ?: '�' }}</p>
            </div>

            <p style="margin-top:24px; color:#475569;">If you have questions, reply to this email. We appreciate your business.</p>
            @if(! empty($emailSignature))
                <p style="margin-top:16px; color:#475569; white-space: pre-line;">{{ $emailSignature }}</p>
            @endif
        </div>
    </body>
</html>

