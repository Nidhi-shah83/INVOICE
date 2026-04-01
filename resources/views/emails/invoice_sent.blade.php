<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Invoice {{ $invoice->invoice_number }}</title>
    </head>
    <body style="font-family: 'Inter', sans-serif; background:#f5f5f0; padding: 32px;">
        <div style="max-width: 640px; margin:auto; background:#fff; padding:32px; border-radius:16px; box-shadow:0 12px 40px rgba(15,23,42,.15);">
            <p style="margin:0 0 6px; font-size:14px; color:#94a3b8;">{{ $businessName }}</p>
            <h1 style="margin:8px 0 16px; color:#0f172a;">Invoice {{ $invoice->invoice_number }}</h1>
            <p style="color:#475569;">Hi {{ $invoice->client->name }},</p>
            <p style="color:#475569;">
                Please find your invoice attached. Make a secure payment using the link below before <strong>{{ $invoice->due_date?->format('F d, Y') }}</strong>.
            </p>
            <a href="{{ $paymentLink }}" style="display:inline-flex; align-items:center; justify-content:center; background:#059669; color:#fff; padding:14px 28px; border-radius:999px; text-decoration:none; font-weight:600; margin-top:16px;">
                Pay Now
            </a>
            <div style="margin-top:32px; color:#1f2937; font-size:14px; line-height:1.6;">
                <p><strong>Amount Due:</strong> ₹{{ number_format($invoice->total, 2) }}</p>
                <p><strong>Due date:</strong> {{ $invoice->due_date?->format('F d, Y') }}</p>
                <p><strong>GSTIN:</strong> {{ config('invoice.gstin') }}</p>
            </div>
            <p style="margin-top:24px; color:#475569;">If you have questions, reply to this email and we’ll help right away.</p>
        </div>
    </body>
</html>
