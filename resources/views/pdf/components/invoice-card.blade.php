@php
    $currencySymbol = $currencySymbol ?? setting('currency_symbol', 'Rs ');
    $formatted = fn ($value) => $currencySymbol . number_format($value, 2);
    $gstRows = [
        'CGST' => $invoice->cgst,
        'SGST' => $invoice->sgst,
        'IGST' => $invoice->igst,
    ];
    $amountPaid = (float) ($invoice->amount_paid ?? 0);
    $amountDue = (float) ($invoice->amount_due ?? max(0, ($invoice->grand_total ?? 0) - $amountPaid));
@endphp

<div class="page">
    <div class="card">
        <div class="gradient-header">
            <div class="header-content">
                <div>
                    <p class="eyebrow">Invoice</p>
                    <h1 style="margin: 0; font-size: 32px; font-weight: 600;">{{ $invoice->invoice_number }}</h1>
                </div>
                <div class="header-meta">
                    <p>Client: {{ $invoice->client->name ?? 'Client' }}</p>
                    <p>{{ $invoice->client->email ?? '' }}</p>
                </div>
            </div>
        </div>

        <div class="section-row">
            <div class="box">
                <p class="eyebrow" style="color: #6b7280;">From</p>
                <p><strong>{{ setting('business_name', 'Invoice Pro') }}</strong></p>
                <p>{{ setting('address', 'Address not set') }}</p>
                <p>GSTIN {{ setting('gstin', '-') }}</p>
                <p>{{ setting('email', 'Email not set') }}</p>
                <p>{{ setting('phone', 'Phone not set') }}</p>
            </div>
            <div class="box">
                <p class="eyebrow" style="color: #6b7280;">Bill To</p>
                <p><strong>{{ $invoice->client->name ?? 'Client' }}</strong></p>
                <p>{{ $invoice->client->address ?? 'Address not set' }}</p>
                <p>{{ $invoice->client->email ?? '' }}</p>
                <p>{{ $invoice->client->phone ?? 'N/A' }}</p>
                <p style="margin-top: 8px;"><strong>Status: </strong>{{ ucfirst((string) ($invoice->status ?? 'draft')) }}</p>
            </div>
        </div>

        <div class="section-grid">
            <div class="small-card">
                <p class="eyebrow" style="color: #6b7280;">Dates</p>
                <p><strong>Issue:</strong> {{ $invoice->issue_date?->format('M d, Y') ?? 'N/A' }}</p>
                <p><strong>Due:</strong> {{ $invoice->due_date?->format('M d, Y') ?? 'N/A' }}</p>
            </div>
            <div class="small-card">
                <p class="eyebrow" style="color: #6b7280;">Payment</p>
                <p><strong>Currency:</strong> {{ $invoice->currency }}</p>
                <p><strong>Terms:</strong> {{ $invoice->payment_terms ?? 'As agreed' }}</p>
            </div>
            <div class="small-card">
                <p class="eyebrow" style="color: #6b7280;">Totals</p>
                <p>Paid: <strong>{{ $formatted($amountPaid) }}</strong></p>
                <p>Due: <strong>{{ $formatted($amountDue) }}</strong></p>
                <p class="grand" style="margin-top: 8px;">{{ $currencySymbol }}{{ number_format($invoice->grand_total ?? 0, 2) }}</p>
            </div>
        </div>

        <div class="items">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>GST %</th>
                        <th class="amount">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ number_format((float) ($item->qty_billed ?? 0), 2) }}</td>
                            <td>{{ $formatted($item->rate ?? 0) }}</td>
                            <td>{{ number_format((float) ($item->gst_percent ?? 0), 2) }}%</td>
                            <td class="amount">{{ $formatted($item->amount ?? 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="summary">
            <div class="summary-card">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>{{ $formatted($invoice->subtotal ?? 0) }}</span>
                </div>
                @foreach($gstRows as $label => $value)
                    <div class="summary-row">
                        <span>{{ $label }}</span>
                        <span>{{ $formatted($value ?? 0) }}</span>
                    </div>
                @endforeach
                @if((float) ($invoice->round_off ?? 0) != 0.0)
                    <div class="summary-row">
                        <span>Round Off</span>
                        <span>{{ $formatted($invoice->round_off) }}</span>
                    </div>
                @endif
                <div class="summary-divider"></div>
                <div class="grand">
                    <span>Grand Total</span>
                    <span>{{ $formatted($invoice->grand_total ?? 0) }}</span>
                </div>
            </div>
        </div>

        <div class="footer-grid">
            <div class="footer-card">
                <span class="label">Reference</span>
                <p><strong>Invoice Type:</strong> {{ ucfirst($invoice->invoice_type ?? 'N/A') }}</p>
                <p><strong>PO #:</strong> {{ $invoice->po_number ?: 'N/A' }}</p>
                <p><strong>Ref #:</strong> {{ $invoice->reference_no ?: 'N/A' }}</p>
            </div>
            <div class="footer-card">
                <span class="label">Bank Details</span>
                <p><strong>Bank:</strong> {{ $invoice->bank_name ?: 'N/A' }}</p>
                <p><strong>Account:</strong> {{ $invoice->account_number ?: 'N/A' }}</p>
                <p><strong>IFSC:</strong> {{ $invoice->ifsc_code ?: 'N/A' }}</p>
            </div>
            <div class="footer-card">
                <span class="label">Notes</span>
                <p>{{ $invoice->notes ?: '—' }}</p>
            </div>
        </div>
    </div>
</div>
