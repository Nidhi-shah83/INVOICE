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
    @php $currencySymbol = config('invoice.currency_symbol', '₹'); @endphp
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Client</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $quote->client->name }}</h1>
                <p class="text-sm text-slate-500">{{ $quote->client->email }} · {{ $quote->client->phone ?? 'Phone not set' }}</p>
                @if($quote->client->address)
                    <p class="mt-3 text-sm text-slate-500">{{ $quote->client->address }}</p>
                @endif
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-semibold">
                    <span class="rounded-full border border-slate-200 px-3 py-1 text-slate-600 uppercase tracking-[0.3em]">
                        {{ ucfirst($quote->status) }}
                    </span>
                    @if($quote->reference_no)
                        <span class="rounded-full border border-slate-200 px-3 py-1 text-slate-600 uppercase tracking-[0.3em]">
                            Ref. {{ $quote->reference_no }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Valid until</p>
                <h2 class="text-xl font-semibold text-slate-900">{{ $quote->validity_date?->format('M j, Y') }}</h2>
                <p class="text-sm text-slate-500">Status: {{ ucfirst($quote->status) }}</p>
                @if($quote->salesperson)
                    <p class="mt-2 text-sm text-slate-500">Salesperson: {{ $quote->salesperson }}</p>
                @endif
                <div class="mt-6 rounded-2xl border border-slate-900 bg-slate-900/95 px-5 py-4 text-center text-white">
                    <p class="text-xs uppercase tracking-[0.3em]">Grand total</p>
                    <p class="text-3xl font-semibold">{{ $currencySymbol }}{{ number_format($quote->grand_total, 2) }} {{ $quote->currency }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Line items</p>
            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-100">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Name</th>
                            <th class="px-4 py-3 text-left font-semibold">Qty</th>
                            <th class="px-4 py-3 text-left font-semibold">Rate</th>
                            <th class="px-4 py-3 text-left font-semibold">GST%</th>
                            <th class="px-4 py-3 text-right font-semibold">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($quote->items as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item->name }}</td>
                                <td class="px-4 py-3">{{ $item->qty }}</td>
                                <td class="px-4 py-3">{{ number_format($item->rate, 2) }}</td>
                                <td class="px-4 py-3">{{ number_format($item->gst_percent, 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ $currencySymbol }}{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div class="space-y-2 rounded-2xl border border-slate-200 p-4 text-sm text-slate-600">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Subtotal</p>
                    <p class="text-lg font-semibold text-slate-900">{{ $currencySymbol }}{{ number_format($quote->subtotal, 2) }}</p>
                    <p class="text-xs text-slate-400">{{ ucfirst($quote->discount_type) }} discount: {{ $quote->discount_type === 'percent' ? number_format($quote->discount_value, 2).'%' : $currencySymbol.number_format($quote->discount_value, 2) }}</p>
                    <p class="text-xs text-slate-400">Discount amount: {{ $currencySymbol }}{{ number_format($quote->discount_amount, 2) }}</p>
                </div>
                <div class="space-y-2 rounded-2xl border border-slate-200 p-4 text-sm text-slate-600">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Taxable</p>
                    <p class="text-lg font-semibold text-slate-900">{{ $currencySymbol }}{{ number_format($quote->subtotal - $quote->discount_amount, 2) }}</p>
                    <div class="grid gap-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span>CGST</span>
                            <span>{{ $currencySymbol }}{{ number_format($quote->cgst, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>SGST</span>
                            <span>{{ $currencySymbol }}{{ number_format($quote->sgst, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>IGST</span>
                            <span>{{ $currencySymbol }}{{ number_format($quote->igst, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="space-y-2 rounded-2xl border border-slate-200 bg-slate-900 p-4 text-sm text-white">
                    <p class="text-xs uppercase tracking-[0.3em] text-white/70">Additional adjustments</p>
                    <div class="flex items-center justify-between">
                        <span>Round off</span>
                        <span>{{ $currencySymbol }}{{ number_format($quote->round_off, 2) }}</span>
                    </div>
                    <div class="text-right">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/70">Grand total</p>
                        <p class="text-3xl font-semibold">{{ $currencySymbol }}{{ number_format($quote->grand_total, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Payment terms</p>
                <p class="mt-2 text-sm text-slate-600">{{ $quote->payment_terms ?? 'No payment terms specified.' }}</p>
            </section>
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Terms & Conditions</p>
                <p class="mt-2 text-sm text-slate-600">{{ $quote->terms_conditions ?? 'No additional terms.' }}</p>
            </section>
        </div>

        @if($quote->notes)
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Notes</p>
                <p class="mt-2 text-sm text-slate-600">{{ $quote->notes }}</p>
            </section>
        @endif
    </div>
@endsection
