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
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('orders.show', $order) }}" class="text-slate-600 hover:text-slate-900 font-semibold">View</a>
                                    @if($order->remaining_amount > 0)
                                        <form method="POST" action="{{ route('orders.createInvoice', $order) }}" onsubmit="return confirmConvertToInvoice({{ $order->id }}, '{{ addslashes($order->quote ? $order->quote->quote_number : 'N/A') }}', '{{ addslashes($order->client->name) }}', {{ $order->remaining_amount }})">
                                            @csrf
                                            @foreach($order->items as $item)
                                                <input type="hidden" name="items[{{ $loop->index }}][order_item_id]" value="{{ $item->id }}">
                                                <input type="hidden" name="items[{{ $loop->index }}][qty]" value="{{ $item->qty_remaining }}">
                                            @endforeach
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-500 font-semibold">Convert to Invoice</button>
                                        </form>
                                    @endif
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
        function confirmConvertToInvoice(orderId, quoteNumber, clientName, remainingAmount) {
            const message = `Convert Order #${orderId} to Invoice?\n\n` +
                `Quote: ${quoteNumber}\n` +
                `Client: ${clientName}\n` +
                `Remaining Amount: ₹${remainingAmount.toFixed(2)}\n\n` +
                `This will create an invoice for all remaining items.`;

            return confirm(message);
        }
    </script>
@endsection
