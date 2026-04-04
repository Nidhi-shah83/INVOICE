<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Order {{ $order->order_number }}</title>
    </head>
    <body style="font-family: 'Inter', sans-serif; background:#f5f5f0; padding: 32px;">
        <div style="max-width: 640px; margin:auto; background:#fff; padding:32px; border-radius:16px; box-shadow:0 12px 40px rgba(15,23,42,.15);">
            @php $currencySymbol = config('invoice.currency_symbol', '₹'); @endphp
            <p style="margin:0 0 6px; font-size:14px; color:#94a3b8;">{{ config('invoice.business_name') }}</p>
            <h1 style="margin:8px 0 16px; color:#0f172a;">Order {{ $order->order_number }}</h1>
            <p style="color:#475569;">Hi {{ $order->client->name }},</p>
            <p style="color:#475569;">
                Please find your order summary attached. The remaining amount is <strong>{{ $currencySymbol }}{{ number_format($order->total_amount - $order->billed_amount, 2) }}</strong>.
                Let us know if you need any changes before we proceed.
            </p>
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
                        @foreach($order->items as $item)
                            <tr>
                                <td style="padding:8px 4px; border-bottom:1px solid #f1f5f9;">{{ $item->name }}</td>
                                <td style="padding:8px 4px; text-align:right; border-bottom:1px solid #f1f5f9;">{{ number_format($item->qty, 2) }}</td>
                                <td style="padding:8px 4px; text-align:right; border-bottom:1px solid #f1f5f9;">{{ $currencySymbol }}{{ number_format($item->rate, 2) }}</td>
                                <td style="padding:8px 4px; text-align:right; border-bottom:1px solid #f1f5f9;">{{ number_format($item->gst_percent, 2) }}%</td>
                                <td style="padding:8px 4px; text-align:right; border-bottom:1px solid #f1f5f9;">{{ $currencySymbol }}{{ number_format($item->qty * $item->rate, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:32px; color:#1f2937; font-size:14px; line-height:1.6;">
                <p><strong>Order Total:</strong> {{ $currencySymbol }}{{ number_format($order->total_amount, 2) }}</p>
                <p><strong>Amount Billed:</strong> {{ $currencySymbol }}{{ number_format($order->billed_amount, 2) }}</p>
                <p><strong>Remaining:</strong> {{ $currencySymbol }}{{ number_format($order->total_amount - $order->billed_amount, 2) }}</p>
                <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
            </div>

            <p style="margin-top:24px; color:#475569;">If you have questions, reply to this email. We appreciate your business.</p>
        </div>
    </body>
</html>
