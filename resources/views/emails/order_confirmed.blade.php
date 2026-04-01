<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Order Confirmed — {{ $order->order_number }}</title>
    </head>
    <body style="font-family: 'Inter', sans-serif; background:#f5f5f0; padding: 28px;">
        <div style="max-width: 640px; margin: auto; background:#fff; padding: 32px; border-radius: 16px; box-shadow: 0 12px 40px rgba(15,23,42,.15);">
            <p style="margin-bottom: 4px; font-size: 14px; color:#94a3b8;">{{ config('invoice.business_name') }}</p>
            <h1 style="margin-top: 0; font-size: 28px; color:#0f172a;">Order Confirmed</h1>
            <p style="color:#475569;">
                Hi {{ $order->client->name }},
            </p>
            <p style="color:#475569;">
                Your order <strong>{{ $order->order_number }}</strong> has been confirmed. Below is a summary of what we will be dispatching:
            </p>
            <ul style="padding-left: 20px; color:#1f2937;">
                @foreach($order->items as $item)
                    <li style="margin-bottom:4px;">
                        {{ $item->name }} · {{ $item->qty }} × ₹{{ number_format($item->rate, 2) }} = ₹{{ number_format($item->qty * $item->rate, 2) }}
                    </li>
                @endforeach
            </ul>
            <p style="margin-top: 20px; font-size: 18px; color:#0f172a;">
                Total: ₹{{ number_format($order->total_amount, 2) }}
            </p>
            <p style="margin-top: 24px; color:#475569; font-size: 15px;">
                Your order has been confirmed. We will send your invoice soon.
            </p>
            <p style="margin-top: 32px; color:#94a3b8; font-size: 12px;">
                Need help? Reply to this email and we'll get right back to you.
            </p>
        </div>
    </body>
</html>
