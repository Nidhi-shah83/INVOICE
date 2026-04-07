<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Invoice {{ $invoice->invoice_number }}</title>
        <style>
            body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #0f172a; }
            .header { border-bottom: 2px solid #1e293b; padding-bottom: 12px; margin-bottom: 14px; }
            .row { width: 100%; }
            .left { float: left; width: 60%; }
            .right { float: right; width: 38%; text-align: right; }
            .clear { clear: both; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #e2e8f0; padding: 8px; }
            th { background: #f8fafc; text-align: left; }
            .totals { margin-top: 14px; width: 40%; margin-left: auto; }
            .totals td { border: none; padding: 4px 0; }
            .muted { color: #64748b; }
        </style>
    </head>
    <body>
        @php
            $currencySymbol = setting('currency_symbol', 'Rs');
            $logo = null;
            $logoSetting = setting('logo') ?: setting('business_logo');
            if ($logoSetting) {
                $logoPath = storage_path('app/public/' . normalize_storage_path($logoSetting));
                if (file_exists($logoPath)) {
                    $logo = base64_encode(file_get_contents($logoPath));
                }
            }
            $grandTotal = (float) ($invoice->grand_total ?? $invoice->total ?? 0);
            $amountPaid = (float) ($invoice->amount_paid ?? 0);
            $amountDue = (float) ($invoice->amount_due ?? max(0, $grandTotal - $amountPaid));
        @endphp

        <div class="header row">
            <div class="left">
                @if($logo)
                    <img src="data:image/png;base64,{{ $logo }}" alt="Logo" style="max-height: 60px; margin-bottom: 8px;">
                @endif
                <h2 style="margin: 0;">INVOICE {{ $invoice->invoice_number }}</h2>
                <p class="muted" style="margin: 4px 0 0;">{{ setting('business_name', 'Invoice Pro') }}</p>
                <p class="muted" style="margin: 2px 0;">{{ setting('address', 'Address not set') }}</p>
                <p class="muted" style="margin: 2px 0;">GSTIN: {{ setting('gstin', '-') }}</p>
            </div>
            <div class="right">
                <p><strong>Status:</strong> {{ ucfirst((string) ($invoice->status ?? 'draft')) }}</p>
                <p><strong>Issue Date:</strong> {{ $invoice->issue_date?->format('d M, Y') }}</p>
                <p><strong>Due Date:</strong> {{ $invoice->due_date?->format('d M, Y') }}</p>
                <p><strong>Client:</strong> {{ $invoice->client->name ?? '-' }}</p>
            </div>
            <div class="clear"></div>
        </div>

        <h4 style="margin: 0 0 6px;">Bill To</h4>
        <p style="margin: 0;">{{ $invoice->client->name ?? '-' }}</p>
        <p style="margin: 2px 0;">{{ $invoice->client->address ?? '-' }}</p>
        <p style="margin: 2px 0;">{{ $invoice->client->email ?? '-' }}</p>
        <p style="margin: 2px 0 10px;">{{ $invoice->client->phone ?? '-' }}</p>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>GST %</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ number_format((float) ($item->qty_billed ?? 0), 2) }}</td>
                        <td>{{ $currencySymbol }}{{ number_format((float) ($item->rate ?? 0), 2) }}</td>
                        <td>{{ number_format((float) ($item->gst_percent ?? 0), 2) }}</td>
                        <td>{{ $currencySymbol }}{{ number_format((float) ($item->amount ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center;">No items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td>Subtotal</td>
                <td style="text-align: right;">{{ $currencySymbol }}{{ number_format((float) ($invoice->subtotal ?? 0), 2) }}</td>
            </tr>
            @if((float) ($invoice->discount_amount ?? 0) > 0)
                <tr>
                    <td>Discount</td>
                    <td style="text-align: right;">{{ $currencySymbol }}{{ number_format((float) $invoice->discount_amount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td>CGST</td>
                <td style="text-align: right;">{{ $currencySymbol }}{{ number_format((float) ($invoice->cgst ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>SGST</td>
                <td style="text-align: right;">{{ $currencySymbol }}{{ number_format((float) ($invoice->sgst ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>IGST</td>
                <td style="text-align: right;">{{ $currencySymbol }}{{ number_format((float) ($invoice->igst ?? 0), 2) }}</td>
            </tr>
            @if((float) ($invoice->round_off ?? 0) != 0.0)
                <tr>
                    <td>Round Off</td>
                    <td style="text-align: right;">{{ $currencySymbol }}{{ number_format((float) $invoice->round_off, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td><strong>Grand Total</strong></td>
                <td style="text-align: right;"><strong>{{ $currencySymbol }}{{ number_format($grandTotal, 2) }}</strong></td>
            </tr>
            <tr>
                <td>Amount Paid</td>
                <td style="text-align: right;">{{ $currencySymbol }}{{ number_format($amountPaid, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Amount Due</strong></td>
                <td style="text-align: right;"><strong>{{ $currencySymbol }}{{ number_format($amountDue, 2) }}</strong></td>
            </tr>
        </table>

        @if(!empty($invoice->notes))
            <h4 style="margin: 14px 0 6px;">Notes</h4>
            <p style="margin: 0;">{{ $invoice->notes }}</p>
        @endif

        @if(!empty($invoice->terms_conditions))
            <h4 style="margin: 12px 0 6px;">Terms &amp; Conditions</h4>
            <p style="margin: 0;">{{ $invoice->terms_conditions }}</p>
        @endif
    </body>
</html>
