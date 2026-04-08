<!DOCTYPE html>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { margin: 0; padding: 0; background: #f1f5f9; font-family: 'DejaVu Sans', sans-serif; }
</style>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Order {{ $order->order_number }}</title>
        <style>
            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }
            .label-pill {
                color: #94a3b8;
                font-size: 10px;
                letter-spacing: 1px;
                text-transform: uppercase;
                margin-bottom: 6px;
                display: block;
            }
            .items-table th {
                background: #1e2a3a;
                color: #ffffff;
                text-transform: uppercase;
                font-size: 11px;
                letter-spacing: 1px;
                padding: 8px 12px;
                text-align: left;
            }
            .items-table td {
                font-size: 12px;
                color: #334155;
                padding: 8px 12px;
                border-bottom: 1px solid #f1f5f9;
                word-wrap: break-word;
                overflow: hidden;
            }
            .items-table td.amount {
                text-align: right;
            }
            .card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 16px;
                margin-bottom: 16px;
            }
            .summary-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 16px;
            }
            .divider {
                border-top: 1px solid #e2e8f0;
                margin: 6px 0;
            }
        </style>
    </head>
    <body>
        @php
            $companyName    = config('company.name', 'Invoice Pro');
            $companyAddress = config('company.address', 'Nil Madhav Nagar');
            $companyGstin   = config('company.gstin', '22ABCDE1234F1Z5');
            $quoteNotes = $order->quote->notes ?? $order->notes ?? '—';
            $quotePaymentTerms = $order->quote->payment_terms ?? $order->payment_terms ?? '—';
        @endphp
        <div style="width:100%; margin:0; padding:0; font-family:'DejaVu Sans',sans-serif; background:#f1f5f9;">
            <div style="background:#1e2a3a; padding:20px; margin:0; border-radius:0; width:100%;">
                <table width="100%" style="table-layout:fixed; width:100%;">
                    <tr>
                        <td width="40%" style="vertical-align:top; word-wrap:break-word; overflow:hidden;">
                            <span class="label-pill" style="color:#94a3b8;">ORDER</span>
                            <span class="header-number" style="color:#ffffff; font-size:24px; font-weight:bold;">
                                {{ $order->order_number ?? '—' }}
                            </span>
                        </td>
                        <td width="5%" style="word-wrap:break-word; overflow:hidden;"></td>
                        <td width="55%" style="text-align:right; vertical-align:top; padding:0 24px 0 20px; word-wrap:break-word; overflow:hidden; color:#ffffff; font-size:11px;">
                            <div style="margin-bottom:4px;">
                                <span style="color:#94a3b8;">Status:</span>
                                <span>{{ $order->status ?? '—' }}</span>
                            </div>
                            <div style="margin-bottom:4px;">
                                <span style="color:#94a3b8;">Date:</span>
                                <span>{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</span>
                            </div>
                            <div>
                                <span style="color:#94a3b8;">Client:</span>
                                <span>{{ $order->client->name ?? '—' }}</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="padding:16px;">
                <div class="card">
                    <table width="100%" style="table-layout:fixed; width:100%;">
                        <tr>
                            <td width="50%" style="word-wrap:break-word; overflow:hidden; vertical-align:top;">
                                <span style="color:#94a3b8; font-size:10px; text-transform:uppercase; letter-spacing:1px;">FROM</span><br>
                                <span style="font-weight:bold; font-size:13px; color:#1e2a3a;">{{ $companyName }}</span><br>
                                <span style="font-size:12px; color:#64748b;">{{ $companyAddress }}</span><br>
                                <span style="font-size:12px; color:#64748b;">GSTIN: {{ $companyGstin }}</span>
                            </td>
                            <td width="50%" style="border-left:1px solid #e2e8f0; padding-left:16px; word-wrap:break-word; overflow:hidden; vertical-align:top;">
                                <span style="color:#94a3b8; font-size:10px; text-transform:uppercase; letter-spacing:1px;">BILL TO</span><br>
                                <span style="font-weight:bold; font-size:13px; color:#1e2a3a;">{{ $order->client->name ?? '—' }}</span><br>
                                <span style="font-size:12px; color:#64748b;">{{ $order->client->address ?? '—' }}</span><br>
                                <span style="font-size:12px; color:#64748b;">{{ $order->client->email ?? '—' }}</span><br>
                                <span style="font-size:12px; color:#64748b;">{{ $order->client->phone ?? '—' }}</span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card">
                    <span class="label-pill">ITEMS</span>
                    <table class="items-table" width="100%" style="table-layout:fixed; width:100%;">
                        <thead>
                            <tr>
                                <th width="30%">Item</th>
                                <th width="12%">Qty</th>
                                <th width="20%">Rate</th>
                                <th width="18%">GST %</th>
                                <th width="20%" class="amount">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                @php
                                    $itemQty = $item->qty ?? $item->quantity ?? 0;
                                    $itemRate = $item->rate ?? 0;
                                    $itemGst = $item->gst_percent ?? $item->gst ?? $item->gst_rate ?? 0;
                                    $itemAmount = $item->total ?? $item->amount ?? $item->line_total ?? ($itemQty * $itemRate);
                                @endphp
                                <tr>
                                    <td>{{ $item->name ?? '—' }}</td>
                                    <td>{{ number_format($itemQty, 2) }}</td>
                                    <td>Rs{{ number_format($itemRate, 2) }}</td>
                                    <td>{{ number_format($itemGst, 2) }}</td>
                                    <td class="amount">Rs{{ number_format($itemAmount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <table width="100%" style="table-layout:fixed; width:100%; margin-bottom:16px;">
                    <tr>
                        <td width="55%"></td>
                        <td width="45%" style="vertical-align:top; padding-left:8px;">
                            <div class="summary-card">
                                @php
                                    $orderTotal = $order->total_amount ?? $order->total ?? $order->grand_total ?? 0;
                                    $billed = $order->billed_amount ?? 0;
                                    $remaining = $order->remaining_amount ?? max(0, ($order->total_amount ?? 0) - ($order->billed_amount ?? 0));
                                @endphp
                                <p class="label-pill">ORDER SUMMARY</p>
                                <table width="100%" style="table-layout:fixed; width:100%; margin-bottom:4px;">
                                    <tr>
                                        <td width="55%" style="font-size:14px; font-weight:bold; color:#1e2a3a;">Order Total</td>
                                        <td width="45%" style="text-align:right; padding-right:6px; font-size:14px; font-weight:bold; color:#1e2a3a;">
                                            Rs{{ number_format($orderTotal, 2) }}
                                        </td>
                                    </tr>
                                </table>
                                <hr style="border:1px solid #e2e8f0; margin:6px 0;">
                                <table width="100%" style="table-layout:fixed; width:100%; margin-bottom:4px;">
                                    <tr>
                                        <td width="55%" style="font-size:12px; color:#334155;">Billed</td>
                                        <td width="45%" style="text-align:right; padding-right:6px; font-size:12px; color:#334155;">
                                            Rs{{ number_format($billed, 2) }}
                                        </td>
                                    </tr>
                                </table>
                                <table width="100%" style="table-layout:fixed; width:100%;">
                                    <tr>
                                        <td width="55%" style="font-size:12px; color:#334155;">Remaining</td>
                                        <td width="45%" style="text-align:right; padding-right:6px; font-size:12px; color:#334155;">
                                            Rs{{ number_format($remaining, 2) }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="card">
                    <table width="100%" style="table-layout:fixed; width:100%;">
                        <tr>
                            <td width="50%" style="word-wrap:break-word; overflow:hidden; vertical-align:top;">
                                <span class="label-pill">NOTES</span>
                                <span style="font-size:12px; color:#334155;">{{ $quoteNotes }}</span>
                            </td>
                            <td width="50%" style="border-left:1px solid #e2e8f0; padding-left:16px; word-wrap:break-word; overflow:hidden; vertical-align:top;">
                                <span class="label-pill">PAYMENT TERMS</span>
                                <span style="font-size:12px; color:#334155;">{{ $quotePaymentTerms }}</span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card" style="margin-bottom:0;">
                    <table width="100%" style="table-layout:fixed; width:100%;">
                        <tr>
                            <td width="100%" style="word-wrap:break-word; overflow:hidden; vertical-align:top;">
                                <span class="label-pill" style="margin-bottom:8px;">Signature</span>
                                
                                <span style="font-size:12px; color:#334155;">{{ $companyName }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>
