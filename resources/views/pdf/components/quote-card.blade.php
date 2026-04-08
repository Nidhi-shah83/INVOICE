@php
    $currencySymbol = $currencySymbol ?? setting('currency_symbol', 'Rs ');
    $formatted = fn ($value) => $currencySymbol . number_format($value, 2);
    $gstRows = [
        'CGST' => $quote->cgst,
        'SGST' => $quote->sgst,
        'IGST' => $quote->igst,
    ];
    $taxableAmount = ($quote->subtotal ?? 0) - ($quote->discount_amount ?? 0);
@endphp

<div class="page">
    <div class="card">
        <div class="gradient-header">
            <div class="header-content">
                <div>
                    <p class="eyebrow">Quote</p>
                    <h1 style="margin: 0; font-size: 32px; font-weight: 600;">{{ $quote->quote_number }}</h1>
                </div>
                <div class="header-meta">
                    <p>Status: {{ ucfirst($quote->status) }}</p>
                    <p>Issued: {{ $quote->issue_date?->format('d M, Y') }}</p>
                    <p>Valid Till: {{ $quote->validity_date?->format('d M, Y') }}</p>
                </div>
            </div>
        </div>

        <div class="section-row">
            <div class="box">
                <p class="eyebrow" style="color: #6b7280;">From</p>
                <p><strong>{{ setting('business_name', 'Invoice Pro') }}</strong></p>
                <p>{{ setting('address', 'Address not set') }}</p>
                <p>GSTIN {{ setting('gstin', '-') }}</p>
                <p>{{ setting('email', '') }}</p>
                <p>{{ setting('phone', '') }}</p>
            </div>
            <div class="box">
                <p class="eyebrow" style="color: #6b7280;">Bill To</p>
                <p><strong>{{ $quote->client->name }}</strong></p>
                <p>{{ $quote->client->address }}</p>
                @if($quote->client->gstin)
                    <p>GSTIN {{ $quote->client->gstin }}</p>
                @endif
                @if($quote->client->email)
                    <p>Email: {{ $quote->client->email }}</p>
                @endif
                @if($quote->client->phone)
                    <p>Phone: {{ $quote->client->phone }}</p>
                @endif
            </div>
        </div>

        <div class="section-grid">
            <div class="small-card">
                <p class="eyebrow" style="color: #6b7280;">Quote Info</p>
                <p><strong>Reference:</strong> {{ $quote->reference_no ?? 'N/A' }}</p>
                <p><strong>Salesperson:</strong> {{ $quote->salesperson ?? 'N/A' }}</p>
            </div>
            <div class="small-card">
                <p class="eyebrow" style="color: #6b7280;">Totals</p>
                <p>Grand Total: <strong>{{ $formatted($quote->grand_total) }}</strong></p>
                <p>Discount: <strong>{{ $formatted($quote->discount_amount) }}</strong></p>
            </div>
        </div>

        <div class="items">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>Discount %</th>
                        <th>GST %</th>
                        <th class="amount">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quote->items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ number_format($item->qty, 3) }}</td>
                            <td>{{ $formatted($item->rate) }}</td>
                            <td>{{ number_format($item->discount_percent ?? 0, 2) }}%</td>
                            <td>{{ number_format($item->gst_percent ?? 0, 2) }}%</td>
                            <td class="amount">{{ $formatted($item->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="summary">
            <div class="summary-card">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>{{ $formatted($quote->subtotal ?? 0) }}</span>
                </div>
                <div class="summary-row">
                    <span>Discount</span>
                    <span>{{ $formatted($quote->discount_amount ?? 0) }}</span>
                </div>
                <div class="summary-row">
                    <span>Taxable Amount</span>
                    <span>{{ $formatted($taxableAmount) }}</span>
                </div>
                @foreach($gstRows as $label => $value)
                    <div class="summary-row">
                        <span>{{ $label }}</span>
                        <span>{{ $formatted($value) }}</span>
                    </div>
                @endforeach
                <div class="summary-divider"></div>
                <div class="grand">
                    <span>Grand Total</span>
                    <span>{{ $formatted($quote->grand_total ?? 0) }}</span>
                </div>
            </div>
        </div>

        <div class="footer-grid">
            <div class="footer-card">
                <span class="label">Notes</span>
                <p>{{ $quote->notes ?? '—' }}</p>
            </div>
            <div class="footer-card">
                <span class="label">Payment Terms</span>
                <p>{{ $quote->payment_terms ?? 'Payment due within 15 days of acceptance' }}</p>
            </div>
            <div class="footer-card">
                <span class="label">Terms &amp; Conditions</span>
                <p>{{ $quote->terms_conditions ?? 'No additional terms.' }}</p>
            </div>
        </div>
    </div>
</div>
