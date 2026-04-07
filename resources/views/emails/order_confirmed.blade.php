<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Order Confirmed - {{ $order->order_number }}</title>
    </head>
    <body style="font-family: Arial, sans-serif; background: #f5f5f0; padding: 24px;">
        @php
            $currencySymbol = setting('currency_symbol', 'Rs');
        @endphp
        <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 24px;">
            <p style="margin: 0 0 4px; color: #64748b;">{{ setting('business_name', 'Invoice Pro') }}</p>
            <h1 style="margin-top: 0;">Order Confirmed</h1>
            <p>Hi {{ $order->client->name }},</p>
            <p>Your order <strong>{{ $order->order_number }}</strong> has been confirmed.</p>
            <ul>
                @foreach($order->items as $item)
                    <li>{{ $item->name }} - {{ $item->qty }} x {{ $currencySymbol }}{{ number_format($item->rate, 2) }}</li>
                @endforeach
            </ul>
            <p><strong>Total:</strong> {{ $currencySymbol }}{{ number_format($order->total_amount, 2) }}</p>
            <p>We will share invoice details shortly.</p>
        </div>
    </body>
</html>
