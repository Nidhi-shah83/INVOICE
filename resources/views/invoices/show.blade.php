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
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold tracking-[0.2em] text-slate-600">
                        {{ strtoupper($invoice->status) }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold tracking-[0.2em] text-emerald-700">
                        {{ strtoupper($invoice->payment_status) }}
                    </span>
                    @if($invoice->is_overdue)
                        <span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold tracking-[0.2em] text-rose-600">
                            Overdue
                        </span>
                    @endif
                </div>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-500">Client</h3>
                    <div class="mt-2 flex items-center gap-3">
                        <div>
                            <p class="text-base font-semibold text-slate-900">{{ $invoice->client->name }}</p>
                            <p class="text-sm text-slate-500">{{ $invoice->client->email }}</p>
                            <p class="text-sm text-slate-500">{{ $invoice->client->phone ?? '—' }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold tracking-[0.3em] text-slate-600">
                            {{ strtoupper($invoice->client->gst_type ?? '—') }}
                        </span>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-slate-500">Dates & Terms</h3>
                    <div class="mt-2 space-y-1 text-sm text-slate-600">
                        <p>Issue: {{ $invoice->issue_date?->format('d M, Y') ?? '—' }}</p>
                        <p>Due: {{ $invoice->due_date?->format('d M, Y') ?? '—' }}</p>
                        <p>Payment terms: {{ $invoice->payment_terms ?? 'As agreed' }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-slate-500">Totals</h3>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Grand total</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ $invoice->formatted_grand_total }}</p>
                        <div class="mt-3 space-y-1 text-sm">
                            <p>Paid: {{ config('invoice.currency_symbol', '₹') }}{{ number_format($invoice->amount_paid, 2) }}</p>
                            <p>Due: <span class="font-semibold text-rose-600">{{ config('invoice.currency_symbol', '₹') }}{{ number_format($invoice->amount_due, 2) }}</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">Pricing breakdown</h2>
            <div class="mt-4 grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                <div class="flex items-center justify-between">
                    <span>Subtotal</span>
                    <span class="font-semibold text-slate-900">{{ config('invoice.currency_symbol', '₹') }}{{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Discount</span>
                    <span class="font-semibold text-slate-900">
                        {{ $invoice->discount_type === 'percent' ? number_format($invoice->discount_value, 2).'%' : config('invoice.currency_symbol', '₹').number_format($invoice->discount_value, 2) }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Discount amount</span>
                    <span class="font-semibold text-slate-900">{{ config('invoice.currency_symbol', '₹') }}{{ number_format($invoice->discount_amount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Taxable amount</span>
                    <span class="font-semibold text-slate-900">{{ config('invoice.currency_symbol', '₹') }}{{ number_format(max(0, $invoice->subtotal - $invoice->discount_amount), 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>CGST</span>
                    <span>{{ config('invoice.currency_symbol', '₹') }}{{ number_format($invoice->cgst, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>SGST</span>
                    <span>{{ config('invoice.currency_symbol', '₹') }}{{ number_format($invoice->sgst, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>IGST</span>
                    <span>{{ config('invoice.currency_symbol', '₹') }}{{ number_format($invoice->igst, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Round off</span>
                    <span>{{ config('invoice.currency_symbol', '₹') }}{{ number_format($invoice->round_off, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">Items</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Item</th>
                            <th class="px-4 py-3 text-right font-semibold">Qty</th>
                            <th class="px-4 py-3 text-right font-semibold">Rate</th>
                            <th class="px-4 py-3 text-right font-semibold">GST%</th>
                            <th class="px-4 py-3 text-right font-semibold">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($invoice->items as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item->name }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item->qty_billed, 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ config('invoice.currency_symbol', '₹') }}{{ number_format($item->rate, 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item->gst_percent, 2) }}%</td>
                                <td class="px-4 py-3 text-right">{{ config('invoice.currency_symbol', '₹') }}{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                <h2 class="text-sm font-semibold text-slate-500">Payment info</h2>
                <div class="space-y-1 text-sm text-slate-600">
                    <p>Due date: {{ $invoice->due_date?->format('d M, Y') ?? '—' }}</p>
                    <p>Currency: {{ $invoice->currency }}</p>
                    <p>Payment link: @if($invoice->payment_link)<a href="{{ $invoice->payment_link }}" target="_blank" class="text-emerald-600 hover:underline">Pay online</a>@else — @endif</p>
                </div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                <h2 class="text-sm font-semibold text-slate-500">Reference & meta</h2>
                <div class="space-y-1 text-sm text-slate-600">
                    <p>Invoice type: {{ ucfirst($invoice->invoice_type) }}</p>
                    <p>PO number: {{ $invoice->po_number ?: '—' }}</p>
                    <p>Reference #: {{ $invoice->reference_no ?: '—' }}</p>
                </div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                <h2 class="text-sm font-semibold text-slate-500">Bank details</h2>
                <div class="space-y-1 text-sm text-slate-600">
                    <p>Bank: {{ $invoice->bank_name ?: '—' }}</p>
                    <p>Account #: {{ $invoice->account_number ?: '—' }}</p>
                    <p>IFSC: {{ $invoice->ifsc_code ?: '—' }}</p>
                    <p>UPI ID: {{ $invoice->upi_id ?: '—' }}</p>
                </div>
            </div>
        </div>

        @if($invoice->terms_conditions || $invoice->notes)
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">Notes & terms</h2>
                <div class="mt-4 space-y-4 text-sm text-slate-600">
                    @if($invoice->notes)
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Notes</p>
                            <p class="mt-1 text-slate-900">{{ $invoice->notes }}</p>
                        </div>
                    @endif
                    @if($invoice->terms_conditions)
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Terms</p>
                            <p class="mt-1 text-slate-900">{{ $invoice->terms_conditions }}</p>
                        </div>
                    @endif
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
                        Amount ({{ config('invoice.currency_symbol', '₹') }})
                        <input
                            type="number"
                            step="0.01"
                            name="amount"
                            value="{{ old('amount', $invoice->amount_due) }}"
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
