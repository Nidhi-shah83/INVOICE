@extends('layouts.app')

@section('page-title', $order->order_number)

@section('content')
    <div class="space-y-6">
        @if(session('status'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Order</p>
                    <h1 class="text-3xl font-semibold text-slate-900">{{ $order->order_number }}</h1>
                    <p class="text-sm text-slate-500">{{ $order->client->name }} · {{ $order->client->email ?? 'Email not set' }}</p>
                </div>
                <div class="space-y-2 text-sm text-slate-600">
                    <div><span class="text-xs uppercase tracking-[0.3em] text-slate-400">Total</span><p class="text-lg font-semibold text-slate-900">₹{{ number_format($order->total_amount, 2) }}</p></div>
                    <div><span class="text-xs uppercase tracking-[0.3em] text-slate-400">Billed</span><p class="text-lg font-semibold text-slate-900">₹{{ number_format($order->billed_amount, 2) }}</p></div>
                    <div><span class="text-xs uppercase tracking-[0.3em] text-slate-400">Remaining</span><p class="text-lg font-semibold text-slate-900">₹{{ number_format($order->remaining_amount, 2) }}</p></div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <form class="flex flex-wrap gap-2" method="POST" action="{{ route('orders.updateStatus', $order) }}">
                    @csrf
                    <select name="status" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        @foreach(['confirmed','in_progress','partially_billed','fulfilled','fully_billed','cancelled'] as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white shadow-lg hover:bg-slate-800 transition">
                        Update status
                    </button>
                </form>

                <form method="POST" action="{{ route('orders.destroy', $order) }}">
                    @csrf
                    @method('DELETE')
                    <button class="text-xs text-rose-600 hover:text-rose-400">Delete order</button>
                </form>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Order Items</p>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Item</th>
                            <th class="px-4 py-3 text-right font-semibold">Qty</th>
                            <th class="px-4 py-3 text-right font-semibold">Remaining</th>
                            <th class="px-4 py-3 text-right font-semibold">Rate</th>
                            <th class="px-4 py-3 text-right font-semibold">GST%</th>
                            <th class="px-4 py-3 text-right font-semibold">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item->name }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item->qty, 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item->qty_remaining, 2) }}</td>
                                <td class="px-4 py-3 text-right">₹{{ number_format($item->rate, 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item->gst_percent, 2) }}%</td>
                                <td class="px-4 py-3 text-right">₹{{ number_format($item->qty * $item->rate, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            @livewire('partial-billing-form', ['order' => $order])
        </div>

        @if($order->invoices->isNotEmpty())
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400 mb-3">Invoices</p>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-900 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Invoice #</th>
                                <th class="px-4 py-3 text-left font-semibold">Status</th>
                                <th class="px-4 py-3 text-right font-semibold">Total</th>
                                <th class="px-4 py-3 text-right font-semibold">Issued</th>
                                <th class="px-4 py-3 text-right font-semibold">Due</th>
                                <th class="px-4 py-3 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($order->invoices as $invoice)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $invoice->invoice_number }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-900">₹{{ number_format($invoice->total, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-slate-500">{{ $invoice->issue_date?->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 text-right text-slate-500">{{ $invoice->due_date?->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="text-slate-600 hover:text-slate-900 font-semibold">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
