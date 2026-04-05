@php
    $displayCurrency = $currencySymbol ?? config('invoice.currency_symbol', '₹');
    $formatted = fn ($value) => $displayCurrency . number_format($value, 2);
    $taxableAmount = $quote->subtotal - $quote->discount_amount;
    $gstRows = [
        'CGST' => $quote->cgst,
        'SGST' => $quote->sgst,
        'IGST' => $quote->igst,
    ];
@endphp

@php
    $businessData = array_merge([
        'business_name' => config('invoice.business_name', 'Invoice Pro'),
        'address' => config('company.address', '123 Corporate Blvd, City, State ZIP'),
        'gstin' => config('invoice.gstin', 'XXX0000XXXX'),
        'email' => config('invoice.email', 'contact@example.com'),
        'phone' => config('invoice.phone', ''),
    ], $businessInfo ?? []);
@endphp

<div class="rounded-[28px] border border-slate-200 bg-white shadow-xl overflow-hidden">
    <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-6 text-white">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.4em] text-slate-300">Quote</p>
                <h1 class="text-3xl font-semibold tracking-tight">{{ $quote->quote_number }}</h1>
            </div>
            <div class="space-y-1 text-sm text-slate-200 text-right">
                <p class="font-semibold">Status: {{ ucfirst($quote->status) }}</p>
                <p>Issued On: {{ $quote->issue_date?->format('d M, Y') }}</p>
                <p>Valid Till: {{ $quote->validity_date?->format('d M, Y') }}</p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 px-6 py-8 lg:grid-cols-[1fr,1fr]">
        <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs uppercase tracking-[0.4em] text-slate-500">From</p>
            <p><strong>{{ $businessData['business_name'] }}</strong></p>
            <p>{{ $businessData['address'] }}</p>
            <p>GSTIN {{ $businessData['gstin'] }}</p>
            @if(!empty($businessData['email']))
                <p>{{ $businessData['email'] }}</p>
            @endif
            @if(!empty($businessData['phone']))
                <p>{{ $businessData['phone'] }}</p>
            @endif
        </div>
        <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs uppercase tracking-[0.4em] text-slate-500">Bill To</p>
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

    <div class="space-y-4 px-6 pb-8">
        <div class="space-y-4 rounded-[24px] border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs uppercase tracking-[0.4em] text-slate-500">Items</p>
                <span class="text-xs text-slate-500">Updated live</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr class="text-left text-xs uppercase tracking-[0.4em]">
                            <th class="px-3 py-3 font-semibold">Item</th>
                            <th class="px-3 py-3 font-semibold">Qty</th>
                            <th class="px-3 py-3 font-semibold">Rate</th>
                            <th class="px-3 py-3 font-semibold">Discount %</th>
                            <th class="px-3 py-3 text-right font-semibold">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($quote->items as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-3">
                                    <p class="font-semibold text-slate-900">{{ $item->name }}</p>
                                    @if($item->description)
                                        <p class="text-xs text-slate-500">{{ $item->description }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3">{{ number_format($item->qty, 3) }}</td>
                                <td class="px-3 py-3">{{ $displayCurrency }}{{ number_format($item->rate, 2) }}</td>
                                <td class="px-3 py-3 text-xs text-slate-500">{{ number_format($item->discount_percent ?? 0, 2) }}%</td>
                                <td class="px-3 py-3 text-right font-semibold text-slate-900">
                                    {{ $displayCurrency }}{{ number_format($item->amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="flex justify-end">
            <div class="w-full max-w-sm space-y-4 rounded-[24px] border border-slate-100 bg-slate-50 p-6 shadow-sm">
                <p class="text-xs uppercase tracking-[0.4em] text-slate-500">Financial Summary</p>
                <div class="space-y-3 text-sm text-slate-700">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span class="font-medium">{{ $formatted($quote->subtotal) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Discount</span>
                    <span class="font-medium">{{ $formatted($quote->discount_amount) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Taxable Amount</span>
                    <span class="font-medium">{{ $formatted($taxableAmount) }}</span>
                </div>
                @foreach ($gstRows as $label => $value)
                    <div class="flex justify-between text-[13px]">
                        <span>{{ $label }}</span>
                        <span>{{ $formatted($value) }}</span>
                    </div>
                @endforeach
                <div class="border-t border-slate-200 pt-3">
                    <div class="flex justify-between text-sm">
                        <span>Round Off</span>
                        <span>{{ $formatted($quote->round_off) }}</span>
                    </div>
                    <div class="mt-3 flex justify-between text-lg font-semibold text-slate-900">
                        <span>GRAND TOTAL</span>
                        <span class="text-xl">{{ $formatted($quote->grand_total) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 px-6 pb-8 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Notes</p>
            <p class="mt-2 text-sm text-slate-700">{{ $quote->notes ?? '—' }}</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Payment Terms</p>
            <p class="mt-2 text-sm text-slate-700">{{ $quote->payment_terms ?? 'Payment due within 15 days of acceptance' }}</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Terms & Conditions</p>
            <p class="mt-2 text-sm text-slate-700">{{ $quote->terms_conditions ?? 'No additional terms.' }}</p>
        </div>
    </div>
</div>
