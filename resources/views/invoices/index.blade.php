@extends('layouts.app')

@section('page-title', 'Invoices')

@section('primary-action')
    <a
        href="{{ route('invoices.create') }}"
        class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-600 transition"
    >
        + Create invoice
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Invoices</p>
                    <h1 class="text-2xl font-semibold text-slate-900">All invoices</h1>
                </div>
            </div>
            <div class="mt-6 flex flex-wrap items-center gap-3 text-sm font-semibold">
                @foreach ($statusTabs as $tab)
                    @php
                        $isActive = $activeStatus === $tab || ($tab === 'all' && !$activeStatus);
                        $tabLabel = ucfirst($tab);
                        $count = $counts[$tab] ?? 0;
                    @endphp
                    <a
                        href="{{ route('invoices.index', ['status' => $tab === 'all' ? null : $tab]) }}"
                        class="inline-flex items-center gap-2 rounded-full border px-4 py-2 {{ $isActive ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 text-slate-600' }}"
                    >
                        {{ $tabLabel }}
                        <span class="text-xs text-slate-400">{{ number_format($count) }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Invoice #</th>
                            <th class="px-4 py-3 text-left font-semibold">Client</th>
                            <th class="px-4 py-3 text-right font-semibold">Grand Total</th>
                            <th class="px-4 py-3 text-right font-semibold">Amount Due</th>
                            <th class="px-4 py-3 text-left font-semibold">Due Date</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-center font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($invoices as $invoice)
                            @php
                                $isOverdue = $invoice->is_overdue;
                                $rowClass = $isOverdue
                                    ? 'bg-rose-50 hover:bg-rose-100'
                                    : ($invoice->payment_status === 'partial' ? 'bg-amber-50 hover:bg-amber-100' : 'hover:bg-slate-50');
                                $currencySymbol = config('invoice.currency_symbol', '₹');
                            @endphp
                            <tr class="transition {{ $rowClass }}">
                                <td class="px-4 py-3 text-slate-900">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="font-semibold text-slate-900 hover:text-emerald-600">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    {{ $invoice->client->name ?? 'Unknown client' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold">
                                    {{ $currencySymbol }}{{ number_format($invoice->grand_total, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-rose-600">
                                    {{ $currencySymbol }}{{ number_format($invoice->amount_due, 2) }}
                                </td>

                                <td class="px-4 py-3 text-slate-600">
                                    {{ $invoice->due_date?->format('d M, Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($isOverdue ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600') }}">
                                        {{ strtoupper($invoice->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 justify-center">
                                        <a href="{{ route('invoices.download', $invoice) }}" class="text-xs text-slate-500 hover:text-emerald-600">PDF</a>
                                        <form action="{{ route('invoices.send', $invoice) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-xs text-slate-500 hover:text-emerald-600">Send</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-6 text-center text-slate-400" colspan="7">
                                    No invoices yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $invoices->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection

