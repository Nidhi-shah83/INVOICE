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
    @php
        $currencySymbol = setting('currency_symbol', 'Rs');
        $statusClasses = [
            'draft' => 'bg-slate-100 text-slate-600',
            'sent' => 'bg-blue-100 text-blue-700',
            'accepted' => 'bg-emerald-100 text-emerald-700',
            'converted' => 'bg-indigo-100 text-indigo-700',
            'declined' => 'bg-rose-100 text-rose-700',
            'expired' => 'bg-rose-100 text-rose-700',
        ];
    @endphp

    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Quotes</p>
                    <h1 class="text-2xl font-semibold text-slate-900">All quotes</h1>
                </div>
            </div>
            <div class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-3 text-sm font-semibold">
                    @foreach ($statusTabs as $tab)
                        @php
                            $isActive = ($activeStatus === $tab) || ($tab === 'all' && !$activeStatus);
                            $tabLabel = ucfirst($tab);
                            $count = $counts[$tab] ?? 0;
                        @endphp
                        <a
                            href="{{ route('quotes.index', ['status' => $tab === 'all' ? null : $tab, 'search' => $search ?: null]) }}"
                            class="inline-flex items-center gap-2 rounded-full border px-4 py-2 {{ $isActive ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 text-slate-600' }}"
                        >
                            {{ $tabLabel }}
                            <span class="text-xs text-slate-400">{{ number_format($count) }}</span>
                        </a>
                    @endforeach
                </div>

                <div class="flex w-full flex-wrap items-center gap-2 lg:w-auto">
                    <form method="GET" action="{{ route('quotes.index') }}" class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
                        @if ($activeStatus && $activeStatus !== 'all')
                            <input type="hidden" name="status" value="{{ $activeStatus }}">
                        @endif
                        <div class="relative w-full">
                            <input
                                type="search"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Search quote # or client..."
                                class="w-full rounded-2xl border border-slate-200 px-4 py-2 pr-10 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-white">
                                Search
                            </button>
                            @unless (blank($search))
                                <a
                                    href="{{ route('quotes.index', ['status' => $activeStatus !== 'all' ? $activeStatus : null]) }}"
                                    class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 hover:border-slate-300"
                                >
                                    Clear
                                </a>
                            @endunless
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Quote #</th>
                            <th class="px-4 py-3 text-left font-semibold">Client</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Issue Date</th>
                            <th class="px-4 py-3 text-left font-semibold">Validity</th>
                            <th class="px-4 py-3 text-right font-semibold">Total</th>
                            <th class="px-4 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($quotes as $quote)
                            @php
                                $validity = $quote->validity_date;
                                $daysUntil = $validity ? now()->diffInDays($validity, false) : null;
                                $isExpired = $validity ? $validity->isPast() : false;
                                $rowClass = $isExpired
                                    ? 'bg-rose-50 hover:bg-rose-100'
                                    : (!is_null($daysUntil) && $daysUntil <= 3 ? 'bg-amber-50 hover:bg-amber-100' : 'hover:bg-slate-50');
                            @endphp
                            <tr class="transition {{ $rowClass }}">
                                <td class="px-4 py-3 font-semibold text-slate-800">
                                    <a href="{{ route('quotes.show', $quote) }}" class="hover:text-emerald-600">
                                        {{ $quote->quote_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-slate-500">
                                    <div>{{ $quote->client->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $quote->client->email }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $statusClasses[$quote->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($quote->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500">{{ $quote->issue_date?->format('d M, Y') }}</td>
                                <td class="px-4 py-3 text-slate-500">
                                    <div>{{ $quote->validity_date?->format('d M, Y') }}</div>
                                    @if ($isExpired)
                                        <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-rose-600">Expired</span>
                                    @elseif(!is_null($daysUntil) && $daysUntil <= 3)
                                        <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-amber-600">Expiring soon</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900">
                                    {{ $currencySymbol }}{{ number_format($quote->grand_total, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-xs font-semibold">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a
                                            href="{{ route('quotes.show', $quote) }}"
                                            class="inline-flex items-center justify-center rounded-full border border-slate-200 px-3 py-1 text-slate-600 hover:border-slate-400 hover:text-slate-900"
                                            aria-label="View quote {{ $quote->quote_number }}"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a
                                            href="{{ route('quotes.edit', $quote) }}"
                                            class="inline-flex items-center justify-center rounded-full border border-slate-200 px-3 py-1 text-slate-600 hover:border-slate-400 hover:text-slate-900"
                                            aria-label="Edit quote {{ $quote->quote_number }}"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4h6m-3 0v6M4 17v3h3l9-9-3-3-9 9z" />
                                            </svg>
                                        </a>
                                        @if ($quote->status !== 'converted')
                                            <form
                                                method="POST"
                                                action="{{ route('quotes.convert', $quote) }}"
                                                class="inline"
                                                data-swal-confirm
                                                data-swal-title="Convert {{ $quote->quote_number }}?"
                                                data-swal-text="This will turn the quote into an order."
                                                data-swal-confirm-button="Convert"
                                                data-swal-cancel-button="Cancel"
                                                data-swal-icon="warning"
                                                data-swal-confirm-color="#10b981"
                                            >
                                                @csrf
                                                <div class="relative group inline-flex">
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center justify-center rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-emerald-700 hover:border-emerald-200 hover:bg-emerald-100"
                                                        aria-label="Convert quote {{ $quote->quote_number }} to order"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h4l3 3-3 3H4m6 0h7a2 2 0 002-2v-2a2 2 0 00-2-2h-7" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l7 0m-3-3l3 3-3 3" />
                                                        </svg>
                                                    </button>
                                                    <span class="pointer-events-none absolute -bottom-8 left-1/2 w-max -translate-x-1/2 rounded-full bg-slate-900 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.3em] text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                                        Convert to Order
                                                    </span>
                                                </div>
                                            </form>
                                        @endif
                                        <form
                                            method="POST"
                                            action="{{ route('quotes.destroy', $quote) }}"
                                            class="inline"
                                            data-swal-confirm
                                            data-swal-title="Delete {{ $quote->quote_number }}?"
                                            data-swal-text="This action cannot be undone."
                                            data-swal-confirm-button="Delete"
                                            data-swal-cancel-button="Cancel"
                                            data-swal-icon="warning"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center justify-center rounded-full border border-rose-100 bg-rose-50 px-3 py-1 text-rose-600 hover:border-rose-200 hover:bg-rose-100"
                                                aria-label="Delete quote {{ $quote->quote_number }}"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M10 11v6m4-6v6M9 7V5h6v2" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">
                                    No quotes yet.
                                </td>
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

