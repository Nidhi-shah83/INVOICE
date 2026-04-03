@extends('layouts.app')

@section('page-title', $quote->quote_number)

@section('primary-action')
    <div class="flex flex-wrap gap-3">
        <form method="POST" action="{{ route('quotes.send', $quote) }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-slate-800 transition">
                Send PDF
            </button>
        </form>
        @if($quote->status === 'accepted')
            <form method="POST" action="{{ route('quotes.convert', $quote) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-600 transition">
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
    <div class="space-y-6">
        @php
            $currencySymbol = config('invoice.currency_symbol', '₹');
        @endphp
        @include('quotes.partials.card', compact('currencySymbol'))
    </div>
@endsection
