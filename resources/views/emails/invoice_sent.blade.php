<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Invoice {{ $invoice->invoice_number }}</title>
    </head>
    <body style="font-family: Arial, sans-serif; background: #f5f5f0; padding: 24px;">
        @php
            $currencySymbol = setting('currency_symbol', 'Rs');
        @endphp
        <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 24px;">
            <p style="margin: 0 0 4px; color: #64748b;">{{ setting('business_name', 'Invoice Pro') }}</p>
            <h1 style="margin-top: 0;">Invoice {{ $invoice->invoice_number }}</h1>
            <p>Hi {{ $invoice->client->name }},</p>
            <p>
                Please find your invoice attached. Total due:
                <strong>{{ $currencySymbol }}{{ number_format($invoice->amount_due, 2) }}</strong>.
            </p>
            <p><strong>Due Date:</strong> {{ $invoice->due_date?->format('F d, Y') }}</p>
            <a href="{{ route('invoices.pay', $invoice->id) }}" style="display: inline-block; background: #059669; color: #ffffff; padding: 10px 18px; border-radius: 9999px; text-decoration: none; font-weight: 600;">
                Make Payment
            </a>

            @if(! empty($emailSignature))
                <p style="margin-top: 18px; white-space: pre-line;">{{ $emailSignature }}</p>
            @endif
        </div>
    </body>
</html>
