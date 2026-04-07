<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Order {{ $order->order_number }}</title>
    </head>
    <body style="font-family: Arial, sans-serif; background: #f5f5f0; padding: 24px;">
        @php
            $currencySymbol = setting('currency_symbol', 'Rs');
        @endphp
        <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 24px;">
            <p style="margin: 0 0 4px; color: #64748b;">{{ setting('business_name', 'Invoice Pro') }}</p>
            <h1 style="margin-top: 0;">Order {{ $order->order_number }}</h1>
            <p>Hi {{ $order->client->name }},</p>
            <p>Please find your order summary attached.</p>
            <p><strong>Order Total:</strong> {{ $currencySymbol }}{{ number_format($order->total_amount, 2) }}</p>
            <p><strong>Amount Billed:</strong> {{ $currencySymbol }}{{ number_format($order->billed_amount, 2) }}</p>
            <p><strong>Remaining:</strong> {{ $currencySymbol }}{{ number_format($order->total_amount - $order->billed_amount, 2) }}</p>
        </div>
    </body>
</html>
