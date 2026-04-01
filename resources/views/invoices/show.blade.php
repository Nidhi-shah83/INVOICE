@extends('layouts.app')

@section('page-title', 'Invoice '.$invoice->invoice_number)

@section('primary-action')
    <div class="flex items-center gap-2">
        <a href="{{ route('invoices.download', $invoice) }}" class="inline-flex items-center gap-2 rounded-full border border-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-900">
            Download PDF
        </a>
        <form action="{{ route('invoices.send', $invoice) }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-white">
                Send Invoice
            </button>
        </form>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Invoice</p>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ $invoice->invoice_number }}</h1>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-400">Status</p>
                    <p class="text-lg font-semibold text-slate-900">{{ strtoupper($invoice->status) }}</p>
                </div>
            </div>
            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                <div>
                    <h3 class="text-sm font-semibold text-slate-500">Client</h3>
                    <p class="text-base font-semibold text-slate-900">{{ $invoice->client->name }}</p>
                    <p class="text-sm text-slate-500">{{ $invoice->client->email }}</p>
                    <p class="text-sm text-slate-500">{{ $invoice->client->phone ?? '' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-500">Dates</h3>
                    <p class="text-sm text-slate-900">Issued: {{ $invoice->issue_date?->format('d M, Y') }}</p>
                    <p class="text-sm text-slate-900">Due: {{ $invoice->due_date?->format('d M, Y') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Item</th>
                            <th class="px-4 py-3 text-right font-semibold">Qty</th>
                            <th class="px-4 py-3 text-right font-semibold">Rate</th>
                            <th class="px-4 py-3 text-right font-semibold">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($invoice->items as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item->name }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item->qty_billed, 2) }}</td>
                                <td class="px-4 py-3 text-right">₹{{ number_format($item->rate, 2) }}</td>
                                <td class="px-4 py-3 text-right">₹{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 space-y-2 text-sm text-slate-600">
                <div class="flex items-center justify-between">
                    <span>Subtotal</span>
                    <span class="font-semibold text-slate-900">₹{{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>CGST</span>
                    <span>₹{{ number_format($invoice->cgst, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>SGST</span>
                    <span>₹{{ number_format($invoice->sgst, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>IGST</span>
                    <span>₹{{ number_format($invoice->igst, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-lg font-semibold text-slate-900">
                    <span>Total</span>
                    <span>₹{{ number_format($invoice->total, 2) }}</span>
                </div>
            </div>
            @if($invoice->notes)
                <div class="mt-4 rounded-2xl border border-dashed border-slate-200 p-4 text-sm text-slate-600">
                    <p class="font-semibold text-slate-900">Notes</p>
                    <p>{{ $invoice->notes }}</p>
                </div>
            @endif
        </div>

        @if($invoice->payments->isNotEmpty())
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">Payments</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    @foreach($invoice->payments as $payment)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $payment->razorpay_payment_id }}</p>
                                <p class="text-xs text-slate-500">{{ $payment->created_at?->format('d M, Y h:i A') }}</p>
                            </div>
                            <span class="font-semibold text-slate-900">₹{{ number_format($payment->amount, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">Manually record payment</h2>
            <p class="text-xs text-slate-500">Use this after capturing payment via Razorpay webhooks or manual collection.</p>
            <form action="{{ route('invoices.markPaidManual', $invoice) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-3">
                    <label class="text-xs font-semibold text-slate-500">
                        Amount (₹)
                        <input
                            type="number"
                            step="0.01"
                            name="amount"
                            value="{{ old('amount', $invoice->total) }}"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        >
                        @error('amount') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </label>
                    <label class="text-xs font-semibold text-slate-500">
                        Razorpay Payment ID
                        <input
                            type="text"
                            name="payment_id"
                            value="{{ old('payment_id') }}"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        >
                        @error('payment_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </label>
                    <label class="text-xs font-semibold text-slate-500">
                        Razorpay Order ID
                        <input
                            type="text"
                            name="order_id"
                            value="{{ old('order_id', $invoice->order?->order_number ?? '') }}"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        >
                        @error('order_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </label>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-white shadow-lg hover:bg-emerald-600 transition">
                        Record Payment
                    </button>
                    <p class="text-xs text-slate-500">This will mark the invoice as paid and trigger the payment confirmation email.</p>
                </div>
            </form>
        </div>
    </div>

@endsection
