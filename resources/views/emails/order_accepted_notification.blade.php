<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Order Accepted Notification</title>
    </head>
    <body style="font-family: Arial, sans-serif; background: #f8fafc; padding: 24px;">
        <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 24px;">
            <h2 style="margin-top: 0;">Order Accepted by Client</h2>
            <p>Order <strong>{{ $order->order_number }}</strong> has been accepted.</p>
            <p>Invoice created: <strong>{{ $invoice->invoice_number }}</strong> (ID: {{ $invoice->id }})</p>
            <p>Client: {{ $order->client?->name }}</p>
        </div>
    </body>
</html>
