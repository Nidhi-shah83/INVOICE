@extends('layouts.app')

@section('page-title', 'Quotes')

@section('primary-action')
    <a
        href="{{ route('quotes.create') }}"
        class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-600 transition"
    >
        + New quote
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-4">
                <div class="space-y-1">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Quotes</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ $pipeline['quotes'] }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Orders</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ $pipeline['orders'] }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Invoices</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ $pipeline['invoices'] }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Paid</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ $pipeline['paid'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex flex-wrap gap-2 text-sm font-semibold text-slate-500">
                @foreach($statusTabs as $tab)
                    <a
                        href="{{ route('quotes.index', ['status' => $tab]) }}"
                        class="px-3 py-2 transition {{ $activeStatus === $tab ? 'rounded-2xl bg-slate-900 text-white' : 'rounded-2xl border border-slate-200 hover:border-slate-900' }}"
                    >
                        {{ ucfirst($tab) }}
                    </a>
                @endforeach
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-100">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Quote #</th>
                            <th class="px-4 py-3 text-left font-semibold">Client</th>
                            <th class="px-4 py-3 text-right font-semibold">Grand total</th>
                            <th class="px-4 py-3 text-left font-semibold">Validity</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($quotes as $quote)
                            @php
                                $validity = $quote->validity_date;
                                $daysUntil = $validity ? now()->diffInDays($validity, false) : null;
                                $isExpired = $validity ? $validity->isPast() : false;
                                $rowClass = $isExpired
                                    ? 'bg-rose-50 hover:bg-rose-100'
                                    : (!is_null($daysUntil) && $daysUntil <= 3 ? 'bg-yellow-50 hover:bg-yellow-100' : 'hover:bg-slate-50');
                            @endphp
                            <tr class="transition {{ $rowClass }}">
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $quote->quote_number }}</td>
                                <td class="px-4 py-3 text-slate-500">
                                    <div>{{ $quote->client->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $quote->client->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ config('invoice.currency_symbol', '₹') }}{{ number_format($quote->grand_total, 2) }}</td>
                                <td class="px-4 py-3 text-slate-500">
                                    <div>{{ $quote->validity_date?->format('M j, Y') }}</div>
                                    @if($isExpired)
                                        <span class="text-xs font-semibold uppercase tracking-[0.3em] text-rose-600">Expired</span>
                                    @elseif(!is_null($daysUntil) && $daysUntil <= 3)
                                        <span class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-600">Expiring soon</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $quote->status === 'accepted' ? 'bg-emerald-100 text-emerald-700' : ($quote->status === 'converted' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600') }}">
                                        {{ ucfirst($quote->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('quotes.show', $quote) }}" class="text-slate-600 hover:text-slate-900 font-semibold">View</a>
                                    <a href="{{ route('quotes.edit', $quote) }}" class="text-slate-600 hover:text-slate-900 font-semibold">Edit</a>
                                    <form class="inline" method="POST" action="{{ route('quotes.convert', $quote) }}">
                                        @csrf
                                        <button type="submit" class="text-emerald-600 hover:text-emerald-500 font-semibold">Convert to Invoice</button>
                                    </form>
                                    <form class="inline" method="POST" action="{{ route('quotes.destroy', $quote) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-500 font-semibold">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">No quotes yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $quotes->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
