<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Quote {{ $quote->quote_number }}</title>
    </head>
    <body style="font-family: Arial, sans-serif; background: #f3f4f6; padding: 24px;">
        @php
            $currencySymbol = setting('currency_symbol', 'Rs');
            $sender = array_filter([
                setting('business_name', 'Invoice Pro'),
                setting('address', 'Address not set'),
                'GSTIN '.setting('gstin', '-'),
                
                setting('phone', ''),
            ]);
        @endphp
        <div style="max-width: 720px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden;">
            <div style="background: #0f172a; color: #ffffff; padding: 24px;">
                <p style="margin: 0; font-size: 12px; letter-spacing: 2px; text-transform: uppercase;">Quote</p>
                <h1 style="margin: 6px 0 0; font-size: 28px;">{{ $quote->quote_number }}</h1>
            </div>

            <div style="padding: 24px;">
                <p style="margin-top: 0;">Hi {{ $quote->client->name }},</p>
                <p>Please find your quote summary below.</p>

                <p style="margin-bottom: 4px; font-weight: 700;">From</p>
                @foreach($sender as $line)
                    <p style="margin: 0 0 4px;">{{ $line }}</p>
                @endforeach

                <table style="width: 100%; border-collapse: collapse; margin-top: 18px; font-size: 13px;">
                    <thead>
                        <tr style="background: #0f172a; color: #ffffff;">
                            <th style="text-align: left; padding: 8px;">Item</th>
                            <th style="text-align: left; padding: 8px;">Qty</th>
                            <th style="text-align: right; padding: 8px;">Rate</th>
                            <th style="text-align: right; padding: 8px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quote->items as $item)
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">{{ $item->name }}</td>
                                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">{{ number_format($item->qty, 2) }}</td>
                                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $currencySymbol }}{{ number_format($item->rate, 2) }}</td>
                                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $currencySymbol }}{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <p style="margin-top: 16px;"><strong>Grand Total:</strong> {{ $currencySymbol }}{{ number_format($quote->grand_total, 2) }}</p>
                @if (!empty($quote->approval_token))
                    @php($approvalUrl = $approveUrl ?? url('/quote/approve/'.$quote->id.'/'.$quote->approval_token))
                    <p style="margin-top: 22px;">
                        <a href="{{ $approvalUrl }}"
                           style="padding:10px 20px;background:#16a34a;color:white;text-decoration:none;border-radius:6px;display:inline-block;">
                           Approve Quote
                        </a>
                    </p>
                @endif
                <p style="margin-top: 16px;">If you have questions, reply to this email.</p>
            </div>
        </div>
    </body>
</html>
