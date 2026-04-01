@extends('layouts.app')

@section('page-title', $quote->quote_number)

@section('primary-action')
    <form method="POST" action="{{ route('quotes.send', $quote) }}">
        @csrf
        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-slate-800 transition">
            Send PDF
        </button>
    </form>
    <form method="POST" action="{{ route('quotes.convert', $quote) }}">
        @csrf
        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-600 transition">
            Convert to Order
        </button>
    </form>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Client</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $quote->client->name }}</h1>
                <p class="text-sm text-slate-500">{{ $quote->client->email }} · {{ $quote->client->phone ?? 'Phone not set' }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Valid until</p>
                <h2 class="text-xl font-semibold text-slate-900">{{ $quote->validity_date?->format('M j, Y') }}</h2>
                <p class="text-sm text-slate-500">Status: {{ ucfirst($quote->status) }}</p>
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
                                <td class="px-4 py-3 text-right">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="space-y-2 rounded-2xl border border-dashed border-slate-200 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Subtotal</p>
                    <p class="text-lg font-semibold text-slate-900">{{ number_format($quote->subtotal, 2) }}</p>
                </div>
                <div class="space-y-2 rounded-2xl border border-dashed border-slate-200 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">CGST</p>
                    <p class="text-lg font-semibold text-slate-900">{{ number_format($quote->cgst, 2) }}</p>
                </div>
                <div class="space-y-2 rounded-2xl border border-dashed border-slate-200 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">SGST</p>
                    <p class="text-lg font-semibold text-slate-900">{{ number_format($quote->sgst, 2) }}</p>
                </div>
                <div class="space-y-2 rounded-2xl border border-dashed border-slate-200 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">IGST</p>
                    <p class="text-lg font-semibold text-slate-900">{{ number_format($quote->igst, 2) }}</p>
                </div>
                <div class="space-y-2 rounded-2xl border border-slate-200 bg-slate-900 p-4 text-white">
                    <p class="text-xs uppercase tracking-[0.3em]">Total</p>
                    <p class="text-2xl font-semibold">{{ number_format($quote->total, 2) }}</p>
                </div>
            </div>
            @if($quote->notes)
                <div class="mt-4 rounded-2xl border border-slate-200 p-4 text-sm text-slate-600">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Notes</p>
                    <p>{{ $quote->notes }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
