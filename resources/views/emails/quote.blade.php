<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Quote {{ $quote->quote_number }}</title>
    </head>
    <body style="font-family: 'Inter', sans-serif; background:#f3f4f6; padding: 32px;">
        @php
            $currencySymbol = config('invoice.currency_symbol', '₹');
            $sender = [
                config('invoice.business_name', 'Your Business'),
                config('invoice.address_line', '123 Corporate Blvd, City, State ZIP'),
                'GSTIN ' . config('invoice.gstin', 'XX0000XXXX'),
                config('invoice.email', 'support@business.com'),
                config('invoice.phone', '+91 00000 00000'),
            ];
            $receiver = array_filter([
                $quote->client->name,
                $quote->client->address,
                optional($quote->client)->gstin ? 'GSTIN ' . $quote->client->gstin : null,
                $quote->client->email ? 'Email: ' . $quote->client->email : null,
                $quote->client->phone ? 'Phone: ' . $quote->client->phone : null,
            ]);
            $taxableAmount = $quote->subtotal - $quote->discount_amount;
            $gstRows = [
                'CGST' => $quote->cgst,
                'SGST' => $quote->sgst,
                'IGST' => $quote->igst,
            ];
        @endphp
        <div style="max-width: 700px; margin: auto;">
            <div
                style="
                    background:#fff;
                    border-radius:28px;
                    overflow:hidden;
                    box-shadow: 0 25px 60px rgba(15,23,42,0.1);
                "
            >
                <div style="background:linear-gradient(135deg,#0f172a,#1e293b); color:#fff; padding:32px;">
                    <p style="font-size:10px; letter-spacing:0.4em; text-transform:uppercase; margin:0 0 8px;">Quote</p>
                    <h1 style="margin:0; font-size:32px;">{{ $quote->quote_number }}</h1>
                    <p style="margin:4px 0 0; font-size:12px;">Status: {{ ucfirst($quote->status) }}</p>
                    <p style="margin:0; font-size:12px;">Issued On: {{ $quote->issue_date?->format('d M, Y') }}</p>
                    <p style="margin:0; font-size:12px;">Valid Till: {{ $quote->validity_date?->format('d M, Y') }}</p>
                </div>

                <div style="display:flex; gap:24px; padding:32px;">
                    <div style="flex:1; background:#f1f5f9; border-radius:16px; padding:16px;">
                        <p style="font-size:11px; letter-spacing:0.4em; text-transform:uppercase; color:#64748b;">From</p>
                        @foreach($sender as $line)
                            <p style="margin:4px 0; font-size:13px;">{{ $line }}</p>
                        @endforeach
                    </div>
                    <div style="flex:1; background:#f1f5f9; border-radius:16px; padding:16px;">
                        <p style="font-size:11px; letter-spacing:0.4em; text-transform:uppercase; color:#64748b;">Bill To</p>
                        @foreach($receiver as $line)
                            <p style="margin:4px 0; font-size:13px;">{{ $line }}</p>
                        @endforeach
                    </div>
                </div>

                <div style="padding:0 32px 32px;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr style="background:#0f172a; color:#fff; font-size:10px; text-transform:uppercase; letter-spacing:0.3em;">
                                <th style="padding:10px; text-align:left;">Item</th>
                                <th style="padding:10px; text-align:left;">Qty</th>
                                <th style="padding:10px; text-align:left;">Rate</th>
                                <th style="padding:10px; text-align:left;">Discount %</th>
                                <th style="padding:10px; text-align:right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quote->items as $item)
                                <tr style="background:#fff; border-bottom:1px solid #e2e8f0;">
                                    <td style="padding:10px;">{{ $item->name }}</td>
                                    <td style="padding:10px;">{{ number_format($item->qty, 3) }}</td>
                                    <td style="padding:10px;">{{ $currencySymbol }}{{ number_format($item->rate, 2) }}</td>
                                    <td style="padding:10px;">{{ number_format($item->discount_percent ?? 0, 2) }}%</td>
                                    <td style="padding:10px; text-align:right;">{{ $currencySymbol }}{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="padding:0 32px 32px;">
                    <div style="background:#f1f5f9; border-radius:20px; padding:20px; box-shadow: inset 0 0 0 1px #e2e8f0; max-width:300px; margin-left:auto;">
                        <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px;">
                            <span>Subtotal</span>
                            <span>{{ $currencySymbol }}{{ number_format($quote->subtotal, 2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px;">
                            <span>Discount</span>
                            <span>{{ $currencySymbol }}{{ number_format($quote->discount_amount, 2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px;">
                            <span>Taxable Amount</span>
                            <span>{{ $currencySymbol }}{{ number_format($taxableAmount, 2) }}</span>
                        </div>
                        @foreach ($gstRows as $label => $value)
                            <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px;">
                                <span>{{ $label }}</span>
                                <span>{{ $currencySymbol }}{{ number_format($value, 2) }}</span>
                            </div>
                        @endforeach
                        <div style="border-top:1px dashed #cbd5f5; margin:12px 0;"></div>
                        <div style="display:flex; justify-content:space-between; font-size:16px; font-weight:600;">
                            <span>Grand total</span>
                            <span>{{ $currencySymbol }}{{ number_format($quote->grand_total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div style="padding:0 32px 32px;">
                    <div style="background:#f1f5f9; border-radius:20px; padding:20px; display:flex; gap:24px; font-size:12px;">
                        <div style="flex:1;">
                            <p style="margin:0 0 4px; letter-spacing:0.3em; text-transform:uppercase; color:#64748b;">Notes</p>
                            <p style="margin:0;">{{ $quote->notes ?? '—' }}</p>
                        </div>
                        <div style="flex:1;">
                            <p style="margin:0 0 4px; letter-spacing:0.3em; text-transform:uppercase; color:#64748b;">Payment Terms</p>
                            <p style="margin:0;">{{ $quote->payment_terms ?? 'Payment due within 15 days of acceptance' }}</p>
                        </div>
                        <div style="flex:1;">
                            <p style="margin:0 0 4px; letter-spacing:0.3em; text-transform:uppercase; color:#64748b;">Terms & Conditions</p>
                            <p style="margin:0;">{{ $quote->terms_conditions ?? 'No additional terms.' }}</p>
                        </div>
                    </div>
                </div>

                <div style="padding:0 32px 32px;">
                    <p style="font-size:13px; color:#94a3b8; margin:0;">If you have questions, reply to this email and we’ll help right away.</p>
                </div>
            </div>
        </div>
    </body>
</html>
