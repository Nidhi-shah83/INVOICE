<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Order {{ $order->order_number }}</title>
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
        @endphp

        <div class="header row">
            <div class="left">
                @if($logo)
                    <img src="data:image/png;base64,{{ $logo }}" alt="Logo" style="max-height: 60px; margin-bottom: 8px;">
                @endif
                <h2 style="margin: 0;">ORDER {{ $order->order_number }}</h2>
                <p class="muted" style="margin: 4px 0 0;">{{ setting('business_name', 'Invoice Pro') }}</p>
                <p class="muted" style="margin: 2px 0;">{{ setting('address', 'Address not set') }}</p>
                <p class="muted" style="margin: 2px 0;">GSTIN: {{ setting('gstin', '-') }}</p>
            </div>
            <div class="right">
                <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
                <p><strong>Date:</strong> {{ $order->created_at?->format('d M, Y') }}</p>
                <p><strong>Client:</strong> {{ $order->client->name }}</p>
            </div>
            <div class="clear"></div>
        </div>

        <h4 style="margin: 0 0 6px;">Bill To</h4>
        <p style="margin: 0;">{{ $order->client->name }}</p>
        <p style="margin: 2px 0;">{{ $order->client->address }}</p>
        <p style="margin: 2px 0;">{{ $order->client->email }}</p>
        <p style="margin: 2px 0 10px;">{{ $order->client->phone }}</p>

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
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ number_format($item->qty, 2) }}</td>
                        <td>{{ $currencySymbol }}{{ number_format($item->rate, 2) }}</td>
                        <td>{{ number_format($item->gst_percent, 2) }}</td>
                        <td>{{ $currencySymbol }}{{ number_format($item->qty * $item->rate, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td><strong>Order Total</strong></td>
                <td style="text-align: right;"><strong>{{ $currencySymbol }}{{ number_format($order->total_amount, 2) }}</strong></td>
            </tr>
            <tr>
                <td>Billed</td>
                <td style="text-align: right;">{{ $currencySymbol }}{{ number_format($order->billed_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Remaining</td>
                <td style="text-align: right;">{{ $currencySymbol }}{{ number_format(max(0, $order->total_amount - $order->billed_amount), 2) }}</td>
            </tr>
        </table>
    </body>
</html>
