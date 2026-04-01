<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Quote {{ $quote->quote_number }}</title>
    </head>
    <body style="font-family: 'Inter', sans-serif; background:#f5f5f0; padding: 32px;">
        <div style="max-width: 640px; margin: auto; background:#fff; padding: 32px; border-radius: 16px; box-shadow: 0 12px 40px rgba(15,23,42,.15);">
            <h1 style="margin-top:0; font-size: 24px; color:#0f172a;">Quote {{ $quote->quote_number }}</h1>
            <p style="color:#334155;">Hi {{ $quote->client->name }},</p>
            <p style="color:#475569;">
                Please review the attached quote from {{ config('invoice.business_name') }}.
                If everything looks good, tap the button below to accept it instantly.
            </p>
            <a
                href="{{ $acceptUrl }}"
                style="display:inline-flex; align-items:center; justify-content:center; gap:.5rem; background:#059669; color:#fff; padding:14px 28px; border-radius:999px; text-decoration:none; font-weight:600; margin-top:16px;"
            >
                Accept This Quote
            </a>
            <div style="margin-top:32px; color:#1f2937; font-size:14px; line-height:1.6;">
                <p><strong>Subtotal:</strong> {{ number_format($quote->subtotal, 2) }}</p>
                <p><strong>CGST:</strong> {{ number_format($quote->cgst, 2) }}</p>
                <p><strong>SGST:</strong> {{ number_format($quote->sgst, 2) }}</p>
                <p><strong>IGST:</strong> {{ number_format($quote->igst, 2) }}</p>
                <p><strong>Total:</strong> {{ number_format($quote->total, 2) }}</p>
            </div>
            <p style="color:#94a3b8; font-size:12px; margin-top:32px;">If you have questions, reply to this email and we’ll help right away.</p>
        </div>
    </body>
</html>
