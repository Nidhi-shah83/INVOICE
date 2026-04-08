<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Invoice {{ $invoice->invoice_number }}</title>
        @include('pdf.components.styles')
    </head>
    <body>
        @include('pdf.components.invoice-card', [
            'invoice' => $invoice,
            'currencySymbol' => setting('currency_symbol', 'Rs '),
        ])
    </body>
</html>
