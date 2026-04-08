<?php

return [
    'business_name' => env('INVOICE_BUSINESS_NAME', 'Your Business Name'),
    'gstin' => env('INVOICE_GSTIN', ''),
    'state' => env('INVOICE_STATE', 'Karnataka'),
    'invoice_prefix' => env('INVOICE_PREFIX', 'INV'),
    'quote_prefix' => env('QUOTE_PREFIX', 'QT'),
    'order_prefix' => env('ORDER_PREFIX', 'ORD'),
    'default_due_days' => env('INVOICE_DEFAULT_DUE_DAYS', 15),
    'overdue_secret' => env('INVOICE_OVERDUE_SECRET', 'changeme'),
    'quote_payment_terms' => env('QUOTE_PAYMENT_TERMS', 'Payment due within 15 days of acceptance'),
    'currency' => env('INVOICE_CURRENCY', 'INR'),
    'currency_symbol' => env('INVOICE_CURRENCY_SYMBOL', '₹'),
];
