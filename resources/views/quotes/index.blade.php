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
        $currencySymbol = config('invoice.currency_symbol', '₹');
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
                                        <a href="{{ route('quotes.show', $quote) }}" class="text-slate-600 hover:text-slate-900">View</a>
                                        <a href="{{ route('quotes.edit', $quote) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                                        @if ($quote->status !== 'converted')
                                            <form method="POST" action="{{ route('quotes.convert', $quote) }}" class="inline js-quote-convert" data-quote-number="{{ $quote->quote_number }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="text-emerald-600 hover:text-emerald-500"
                                                >
                                                    Convert to Order
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('quotes.destroy', $quote) }}" class="inline js-quote-delete" data-quote-number="{{ $quote->quote_number }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="text-rose-600 hover:text-rose-500"
                                            >
                                                Delete
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
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const convertForms = document.querySelectorAll('.js-quote-convert');
        convertForms.forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const number = form.dataset.quoteNumber ?? 'quote';
                Swal.fire({
                    title: `Convert ${number}?`,
                    text: 'This will turn the quote into an order.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Convert',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        const deleteForms = document.querySelectorAll('.js-quote-delete');
        deleteForms.forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const number = form.dataset.quoteNumber ?? 'quote';
                Swal.fire({
                    title: `Delete ${number}?`,
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
</div>
@endsection
