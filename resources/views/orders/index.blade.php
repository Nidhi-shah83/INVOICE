@extends('layouts.app')

@section('page-title', 'Orders')

@section('content')
    <div class="space-y-6">
        @if(session('status'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Orders</p>
                    <h1 class="text-2xl font-semibold text-slate-900">All orders</h1>
                </div>
            </div>
            <div class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-3 text-sm font-semibold">
                    @foreach ($statusTabs as $tab)
                        @php
                            $tabValue = $tab === 'all' ? null : $tab;
                            $isActive = ($activeStatus === $tabValue);
                        @endphp
                        <a
                            href="{{ route('orders.index', ['status' => $tabValue, 'search' => $search ?: null]) }}"
                            class="inline-flex items-center gap-2 rounded-full border px-4 py-2 {{ $isActive ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 text-slate-600' }}"
                        >
                            {{ ucfirst($tab) }}
                        </a>
                    @endforeach
                </div>
                <div class="flex w-full flex-wrap items-center gap-2 lg:w-auto">
                    <form method="GET" action="{{ route('orders.index') }}" class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
                        @if ($activeStatus)
                            <input type="hidden" name="status" value="{{ $activeStatus }}">
                        @endif
                        <div class="relative w-full">
                            <input
                                type="search"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Search order # or client..."
                                class="w-full rounded-2xl border border-slate-200 px-4 py-2 pr-10 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-white">
                                Search
                            </button>
                            @unless (blank($search))
                                <a
                                    href="{{ route('orders.index', ['status' => $activeStatus]) }}"
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

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm mt-6">
            <div class="overflow-hidden rounded-2xl border border-slate-100">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Order #</th>
                            <th class="px-4 py-3 text-left font-semibold">Client</th>
                            <th class="px-4 py-3 text-right font-semibold">Total</th>
                            <th class="px-4 py-3 text-right font-semibold">Billed</th>
                            <th class="px-4 py-3 text-right font-semibold">Remaining</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($orders as $order)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $order->order_number }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $order->client->name }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900">₹{{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-4 py-3 text-right text-slate-500">₹{{ number_format($order->billed_amount, 2) }}</td>
                                <td class="px-4 py-3 text-right text-slate-500">₹{{ number_format($order->remaining_amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $order->status === 'fully_billed' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="relative group inline-flex">
                                            <a
                                                href="{{ route('orders.show', $order) }}"
                                                class="inline-flex items-center justify-center rounded-full border border-slate-200 px-3 py-1 text-slate-600 hover:border-slate-400 hover:text-slate-900"
                                                aria-label="View order {{ $order->order_number }}"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <span class="pointer-events-none absolute -bottom-8 left-1/2 w-max -translate-x-1/2 rounded-full bg-slate-900 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.3em] text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                                View
                                            </span>
                                        </div>
                                        @if($order->remaining_amount > 0)
                                            <div class="relative group inline-flex">
                                                <form
                                                    method="POST"
                                                    action="{{ route('orders.createInvoice', $order) }}"
                                                    class="convert-invoice-form"
                                                    data-order-id="{{ $order->id }}"
                                                    data-quote-number="{{ e($order->quote?->quote_number ?? 'N/A') }}"
                                                    data-client-name="{{ e($order->client->name) }}"
                                                    data-remaining-amount="{{ number_format($order->remaining_amount, 2, '.', '') }}"
                                                >
                                                    @csrf
                                                    @foreach($order->items as $item)
                                                        <input type="hidden" name="items[{{ $loop->index }}][order_item_id]" value="{{ $item->id }}">
                                                        <input type="hidden" name="items[{{ $loop->index }}][qty]" value="{{ $item->qty_remaining }}">
                                                    @endforeach
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center justify-center rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-emerald-700 hover:border-emerald-200 hover:bg-emerald-100"
                                                        aria-label="Convert order {{ $order->order_number }} to invoice"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h4l3 3-3 3H4m6 0h7a2 2 0 002-2v-2a2 2 0 00-2-2h-7" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l7 0m-3-3l3 3-3 3" />
                                                        </svg>
                                                    </button>
                                                </form>
                                                <span class="pointer-events-none absolute -bottom-8 left-1/2 w-max -translate-x-1/2 rounded-full bg-slate-900 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.3em] text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                                    Convert
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">No orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $orders->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.convert-invoice-form').forEach(form => {
                form.addEventListener('submit', event => handleConvertForm(event, form));
            });
        });

        function handleConvertForm(event, form) {
            event.preventDefault();

            if (!window.Swal) {
                form.submit();
                return;
            }

            const orderId = form.dataset.orderId;
            const quoteNumber = form.dataset.quoteNumber || 'N/A';
            const clientName = form.dataset.clientName || 'N/A';
            const remainingAmount = Number(form.dataset.remainingAmount) || 0;

            Swal.fire({
                title: `Convert Order #${orderId}?`,
                html:
                    `<p style="margin-bottom: 6px;">Quote: ${quoteNumber}</p>` +
                    `<p style="margin-bottom: 6px;">Client: ${clientName}</p>` +
                    `<p style="margin-bottom: 6px;">Remaining amount: ${formatCurrency(remainingAmount)}</p>` +
                    `<p style="margin-top: 8px; color: #4b5563;">This will create an invoice for all remaining items.</p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Create invoice',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
            }).then(result => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        function formatCurrency(value) {
            return `₹${value.toFixed(2)}`;
        }
    </script>
@endsection
