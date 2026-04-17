<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Payment Confirmed — {{ $invoice->invoice_number }}</title>
    </head>
    <body style="font-family:'Inter',sans-serif;background:#f5f5f0;padding:32px;">
        <div style="max-width:640px;margin:auto;background:#fff;padding:32px;border-radius:16px;box-shadow:0 12px 40px rgba(15,23,42,.15);">
            <h1 style="margin:8px 0 16px;color:#0f172a;">Payment Received</h1>
            <p style="color:#475569;">Hi {{ $invoice->client->name }},</p>
            <p style="color:#475569;">Thank you for your payment of ₹{{ number_format($payment->amount, 2) }} for invoice {{ $invoice->invoice_number }} on {{ $payment->created_at->format('F d, Y') }}.</p>
            <p style="color:#475569;">We appreciate your business and will email you the next invoice soon.</p>
            <div style="margin-top:24px;color:#1f2937;font-size:14px;line-height:1.6;">
                <p><strong>Amount Paid:</strong> ₹{{ number_format($payment->amount, 2) }}</p>
                <p><strong>Payment ID:</strong> {{ $payment->razorpay_payment_id }}</p>
                <p><strong>Invoice:</strong> {{ $invoice->invoice_number }}</p>
            </div>
            @if(! empty($emailSignature))
                <p style="margin-top:24px;color:#475569;white-space: pre-line;">{{ $emailSignature }}</p>
            @endif
        </div>
    </body>
</html>
