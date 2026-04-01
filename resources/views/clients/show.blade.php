@extends('layouts.app')

@section('page-title', $client->name)

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Client</p>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ $client->name }}</h1>
                    <p class="text-sm text-slate-500">{{ $client->email }} · {{ $client->phone ?? 'Phone not set' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-1 text-xs font-semibold text-slate-500">
                        GST Type:
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $client->gst_type === 'intra' ? 'bg-sky-100 text-sky-700' : 'bg-purple-100 text-purple-700' }}">
                            {{ $client->gst_type === 'intra' ? 'CGST + SGST' : 'IGST' }}
                        </span>
                    </span>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">GSTIN</p>
                    <p class="text-lg font-semibold text-slate-900">{{ $client->gstin ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">State</p>
                    <p class="text-lg font-semibold text-slate-900">{{ $client->state }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Address</p>
                    <p class="text-base text-slate-500">{{ $client->address ?? 'No address provided' }}</p>
                </div>
            </div>
        </div>

        <div x-data="{ tab: 'quotes' }" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex gap-2 border-b border-slate-100 pb-3 text-sm font-semibold text-slate-600">
                <button @click="tab = 'quotes'" :class="{ 'border-b-2 border-emerald-500 text-slate-900': tab === 'quotes' }" class="pb-2">Quotes</button>
                <button @click="tab = 'orders'" :class="{ 'border-b-2 border-emerald-500 text-slate-900': tab === 'orders' }" class="pb-2">Orders</button>
                <button @click="tab = 'invoices'" :class="{ 'border-b-2 border-emerald-500 text-slate-900': tab === 'invoices' }" class="pb-2">Invoices</button>
            </div>

            <template x-if="tab === 'quotes'">
                <div>
                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-900 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Quote #</th>
                                    <th class="px-4 py-3 text-left font-semibold">Issue</th>
                                    <th class="px-4 py-3 text-left font-semibold">Validity</th>
                                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($client->quotes as $quote)
                                    <tr>
                                        <td class="px-4 py-3">{{ $quote->quote_number }}</td>
                                        <td class="px-4 py-3">{{ $quote->issue_date->format('M j, Y') }}</td>
                                        <td class="px-4 py-3">{{ $quote->validity_date->format('M j, Y') }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize {{ $quote->status === 'accepted' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $quote->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">{{ number_format($quote->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">
                                            No quotes yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            <template x-if="tab === 'orders'">
                <div>
                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-900 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Order #</th>
                                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold">Total</th>
                                    <th class="px-4 py-3 text-right font-semibold">Billed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($client->orders as $order)
                                    <tr>
                                        <td class="px-4 py-3">{{ $order->order_number }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize {{ in_array($order->status, ['fulfilled', 'fully_billed']) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">{{ number_format($order->total_amount, 2) }}</td>
                                        <td class="px-4 py-3 text-right">{{ number_format($order->billed_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">
                                            No orders yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            <template x-if="tab === 'invoices'">
                <div>
                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-900 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Invoice #</th>
                                    <th class="px-4 py-3 text-left font-semibold">Issue</th>
                                    <th class="px-4 py-3 text-left font-semibold">Due</th>
                                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($client->invoices as $invoice)
                                    <tr>
                                        <td class="px-4 py-3">{{ $invoice->invoice_number }}</td>
                                        <td class="px-4 py-3">{{ $invoice->issue_date->format('M j, Y') }}</td>
                                        <td class="px-4 py-3">{{ $invoice->due_date->format('M j, Y') }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $invoice->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">{{ number_format($invoice->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">
                                            No invoices yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>
    </div>
@endsection
