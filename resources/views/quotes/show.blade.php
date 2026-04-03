@extends('layouts.app')

@section('page-title', $quote->quote_number)

@section('primary-action')
    <div class="flex flex-wrap gap-3">
        <form method="POST" action="{{ route('quotes.send', $quote) }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-slate-800 transition">
                Send PDF
            </button>
        </form>
        @if($quote->status === 'accepted')
            <form method="POST" action="{{ route('quotes.convert', $quote) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-600 transition">
                    Convert to Order
                </button>
            </form>
        @endif
        <a
            href="{{ route('quotes.download', $quote) }}"
            class="inline-flex items-center gap-2 rounded-full border border-slate-900 px-5 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-900 hover:text-white transition"
        >
            Download PDF
        </a>
    </div>
@endsection

@section('content')
    @php
        $currencySymbol = config('invoice.currency_symbol', '₹');
        $businessName = config('invoice.business_name', 'Your Business');
        $businessGstin = config('invoice.gstin', 'GSTIN');
        $businessAddress = config('invoice.address_line') ?? config('invoice.business_name');
        $paymentTerms = $quote->payment_terms ?? 'Payment due within 15 days of acceptance';
        $discountLabel = $quote->discount_type === 'percent'
            ? number_format($quote->discount_value, 2).'%'
            : $currencySymbol.number_format($quote->discount_value, 2);
        $taxableAmount = $quote->subtotal - $quote->discount_amount;
        $gstRows = [
            'cgst' => $quote->cgst,
            'sgst' => $quote->sgst,
            'igst' => $quote->igst,
        ];
    @endphp

    <div class="space-y-6">
        <div class="rounded-[26px] border border-slate-200 bg-white shadow-xl">
            <div class="flex flex-col gap-6 bg-[#0f172a] px-8 py-7 text-white md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-200">Quote</p>
                    <h1 class="text-2xl font-semibold tracking-tight">{{ strtoupper($quote->quote_number) }}</h1>
                    <p class="text-sm text-slate-300">Issued on {{ $quote->issue_date?->format('d M, Y') }}</p>
                </div>
                <div class="text-right space-y-1">
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-400">Status</p>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/60 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em]">
                        {{ ucfirst($quote->status) }}
                    </span>
                    <p class="text-sm text-slate-300">Valid until {{ $quote->validity_date?->format('d M, Y') }}</p>
                </div>
            </div>
            <div class="grid gap-6 px-8 pb-8 pt-6 md:grid-cols-2">
                <div class="space-y-2">
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-400">From</p>
                    <h2 class="text-lg font-semibold text-slate-900">{{ $businessName }}</h2>
                    <p class="text-sm text-slate-600">{{ $businessAddress }}</p>
                    <p class="text-sm text-slate-600">GSTIN {{ $businessGstin }}</p>
                    <p class="text-sm text-slate-600">{{ config('invoice.email', 'support@company.com') }}</p>
                    <p class="text-sm text-slate-600">{{ config('invoice.phone', '+91 00000 00000') }}</p>
                </div>
                <div class="space-y-2">
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-400">Bill to</p>
                    <h2 class="text-lg font-semibold text-slate-900">{{ $quote->client->name }}</h2>
                    <p class="text-sm text-slate-600">{{ $quote->client->email }}</p>
                    <p class="text-sm text-slate-600">{{ $quote->client->phone ?? 'Phone not set' }}</p>
                    <p class="text-sm text-slate-600">{{ $quote->client->address }}</p>
                    <p class="text-sm text-slate-600">GSTIN {{ $quote->client->gstin ?? 'Not provided' }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.7fr,1fr]">
            <div class="rounded-[24px] border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.4em] text-slate-400">Items</p>
                        <h3 class="text-lg font-semibold text-slate-900">Line Items</h3>
                    </div>
                    <span class="text-xs font-semibold text-slate-600">Live calculations</span>
                </div>
                <div class="overflow-x-auto px-6 pb-6 pt-4">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-900 text-white">
                            <tr class="text-left text-xs uppercase tracking-[0.4em]">
                                <th class="px-3 py-3 font-semibold">Item</th>
                                <th class="px-3 py-3 font-semibold">Qty</th>
                                <th class="px-3 py-3 font-semibold">Rate</th>
                                <th class="px-3 py-3 font-semibold">GST%</th>
                                <th class="px-3 py-3 text-right font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($quote->items as $item)
                                <tr>
                                    <td class="px-3 py-3">
                                        <p class="font-semibold text-slate-900">{{ $item->name }}</p>
                                        @if($item->description)
                                            <p class="text-xs text-slate-500">{{ $item->description }}</p>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">{{ number_format($item->qty, 2) }}</td>
                                    <td class="px-3 py-3">{{ $currencySymbol }}{{ number_format($item->rate, 2) }}</td>
                                    <td class="px-3 py-3">{{ number_format($item->gst_percent, 2) }}%</td>
                                    <td class="px-3 py-3 text-right font-semibold text-slate-900">
                                        {{ $currencySymbol }}{{ number_format($item->amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-[24px] border border-slate-200 bg-slate-50 px-6 py-5 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-400">Summary</p>
                    <h3 class="text-lg font-semibold text-slate-900">Quote totals</h3>
                    <p class="text-xs text-slate-500">Live numbers update as you edit line items.</p>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span class="font-semibold text-slate-900">{{ $currencySymbol }}{{ number_format($quote->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Discount ({{ ucfirst($quote->discount_type ?? 'flat') }} {{ $discountLabel }})</span>
                            <span class="font-semibold text-slate-900">{{ $currencySymbol }}{{ number_format($quote->discount_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Taxable amount</span>
                            <span class="font-semibold text-slate-900">{{ $currencySymbol }}{{ number_format($taxableAmount, 2) }}</span>
                        </div>
                        @foreach($gstRows as $key => $value)
                            @if($value > 0)
                                <div class="flex justify-between">
                                    <span class="uppercase tracking-[0.3em] text-[0.6rem] text-slate-500">{{ strtoupper($key) }}</span>
                                    <span class="font-semibold text-slate-900">{{ $currencySymbol }}{{ number_format($value, 2) }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white px-6 py-5 shadow-sm">
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>Round off</span>
                        <span class="font-semibold text-slate-900">{{ $currencySymbol }}{{ number_format($quote->round_off, 2) }}</span>
                    </div>
                    <div class="mt-4 rounded-2xl border border-slate-900 bg-slate-900 px-4 py-5 text-white shadow-lg">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/70">Grand total</p>
                        <p class="text-3xl font-semibold">{{ $currencySymbol }}{{ number_format($quote->grand_total, 2) }}</p>
                        <p class="text-xs text-white/70">{{ $quote->currency }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-[24px] border border-slate-200 bg-white px-6 py-5 shadow-sm">
            <div class="grid gap-4 lg:grid-cols-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-400">Notes</p>
                    <p class="mt-2 text-sm text-slate-600">{{ $quote->notes ?? 'No notes provided.' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-400">Payment terms</p>
                    <p class="mt-2 text-sm text-slate-600">{{ $paymentTerms }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-400">Terms & Conditions</p>
                    <p class="mt-2 text-sm text-slate-600">{{ $quote->terms_conditions ?? 'No additional terms.' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
