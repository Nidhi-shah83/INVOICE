<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Quote {{ $quote->quote_number }}</title>
        @include('pdf.components.styles')
    </head>
    <body>
        @include('pdf.components.quote-card', [
            'quote' => $quote,
            'currencySymbol' => setting('currency_symbol', 'Rs '),
        ])
    </body>
</html>
