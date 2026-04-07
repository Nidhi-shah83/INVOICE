<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Order {{ $order->order_number }}</title>
    </head>
    <body style="font-family: Arial, sans-serif; background: #f5f5f0; padding: 24px;">
        @php
            $currencySymbol = setting('currency_symbol', 'Rs');
            $acceptUrl = url('/order/accept/'.$order->id.'/'.$order->acceptance_token);
            $rejectUrl = url('/order/reject/'.$order->id.'/'.$order->acceptance_token);
        @endphp
        <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 24px;">
            <p style="margin: 0 0 4px; color: #64748b;">{{ setting('business_name', 'Invoice Pro') }}</p>
            <h1 style="margin-top: 0;">Order {{ $order->order_number }}</h1>
            <p>Hi {{ $order->client->name }},</p>
            <p>Please review your order details below.</p>

            <p><strong>Order Total:</strong> {{ $currencySymbol }}{{ number_format($order->total_amount, 2) }}</p>
            <p><strong>Amount Billed:</strong> {{ $currencySymbol }}{{ number_format($order->billed_amount, 2) }}</p>
            <p><strong>Remaining:</strong> {{ $currencySymbol }}{{ number_format($order->total_amount - $order->billed_amount, 2) }}</p>

            @if(! empty($order->acceptance_token))
                <div style="margin-top: 24px;">
                    <a href="{{ $acceptUrl }}"
                       style="padding:12px 24px;background:#16a34a;color:white;text-decoration:none;border-radius:6px;display:inline-block;">
                       Accept Order
                    </a>
                    <a href="{{ $rejectUrl }}"
                       style="padding:12px 24px;background:#dc2626;color:white;text-decoration:none;border-radius:6px;display:inline-block;margin-left:8px;">
                       Reject Order
                    </a>
                </div>
                <p style="margin-top: 10px; color: #64748b; font-size: 12px;">
                    These links are valid for 24 hours from order creation.
                </p>
            @endif
        </div>
    </body>
</html>
