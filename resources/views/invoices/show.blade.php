@extends('layouts.app')

@section('page-title', 'Invoice '.$invoice->invoice_number)

@php
    $viewOnly = request()->boolean('view');
    $currencySymbol = setting('currency_symbol', config('invoice.currency_symbol', 'Rs '));
@endphp

@section('primary-action')
    @unless($viewOnly)
        <div class="flex items-center gap-2">
            <a href="{{ route('invoices.download', $invoice) }}" class="inline-flex items-center gap-2 rounded-full border border-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-900 js-download-pdf" data-invoice-number="{{ $invoice->invoice_number }}">
                Download PDF
            </a>
            <form action="{{ route('invoices.send', $invoice) }}" method="POST" data-prevent-double-submit="true" class="js-send-invoice" data-invoice-number="{{ $invoice->invoice_number }}" data-client-name="{{ $invoice->client->name ?? 'client' }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-white" data-loading-text="Sending...">
                    Send Invoice
                </button>
            </form>
        </div>
    @endunless
@endsection

@section('content')
    <div
        class="space-y-6"
        x-data="invoicePaymentManager({
            markPaidUrl: @js(route('invoices.markPaidManual', $invoice)),
            amountDue: @js((float) $invoice->amount_due),
            amountPaid: @js((float) $invoice->amount_paid),
            grandTotal: @js((float) $invoice->grand_total),
            paymentStatus: @js((string) $invoice->payment_status),
            invoiceStatus: @js((string) $invoice->status),
            isOverdue: @js((bool) $invoice->is_overdue),
            currencySymbol: @js(setting('currency_symbol', config('invoice.currency_symbol', 'Rs '))),
            defaultOrderId: @js((string) ($invoice->order?->order_number ?? $invoice->razorpay_order_id ?? $invoice->invoice_number)),
            invoiceNumber: @js($invoice->invoice_number),
        })"
    >
        <div class="rounded-[28px] border border-slate-200 bg-white shadow-xl overflow-hidden">
            <!-- Header Section with Gradient Background -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-6 text-white">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.4em] text-slate-300">Invoice</p>
                        <h1 class="text-3xl font-semibold tracking-tight">{{ $invoice->invoice_number }}</h1>
                    </div>
                    <div class="space-y-1 text-sm text-slate-200 text-right">
                        <p class="font-semibold" :class="paymentBadgeClass()" x-text="'Status: ' + paymentStatus.toUpperCase()"></p>
                        <p>Client: {{ $invoice->client->name }}</p>
                        <p>{{ $invoice->client->email }}</p>
                    </div>
                </div>
            </div>

            <!-- From and Bill To Sections -->
            <div class="grid gap-6 px-6 py-8 lg:grid-cols-[1fr,1fr]">
                <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-500">From</p>
                    <p><strong>{{ setting('business_name') }}</strong></p>
                    <p>{{ setting('address', config('invoice.address_line', '123 Corporate Blvd, City, State ZIP')) }}</p>
                    <p>GSTIN {{ setting('gstin') }}</p>
                    <p>{{ config('invoice.email') }}</p>
                    <p>{{ config('invoice.phone') }}</p>
                </div>
                <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-500">Bill To</p>
                    <p><strong>{{ $invoice->client->name }}</strong></p>
                    <p>{{ $invoice->client->address ?? 'Address not set' }}</p>
                    <p>{{ $invoice->client->email }}</p>
                    <p>{{ $invoice->client->phone ?? 'N/A' }}</p>
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold tracking-[0.3em] text-emerald-700 mt-2">
                        {{ strtoupper($invoice->client->gst_type ?? 'N/A') }}
                    </span>
                </div>
            </div>

            <!-- Invoice Details Section -->
            <div class="grid gap-6 px-6 py-4 border-t border-slate-100 lg:grid-cols-3">
                <div class="space-y-2">
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-500">Dates</p>
                    <p class="text-sm"><strong>Issue:</strong> {{ $invoice->issue_date?->format('M d, Y') ?? 'N/A' }}</p>
                    <p class="text-sm"><strong>Due:</strong> {{ $invoice->due_date?->format('M d, Y') ?? 'N/A' }}</p>
                </div>
                <div class="space-y-2">
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-500">Payment Info</p>
                    <p class="text-sm"><strong>Currency:</strong> {{ $invoice->currency }}</p>
                    <p class="text-sm"><strong>Terms:</strong> {{ $invoice->payment_terms ?? 'As agreed' }}</p>
                </div>
                <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-500">Totals</p>
                    <div class="space-y-1 text-sm">
                        <p>Paid: <span x-text="formatCurrency(amountPaid)" class="font-semibold text-emerald-600"></span></p>
                        <p>Due: <span x-text="formatCurrency(amountDue)" class="font-semibold text-red-600"></span></p>
                        <p class="border-t border-slate-200 pt-1 mt-1 font-semibold text-lg">{{ $invoice->formatted_grand_total }}</p>
                    </div>
                </div>
            </div>

            <!-- Items Table Section -->
            <div class="space-y-4 px-6 py-8 border-t border-slate-100">
                <p class="text-xs uppercase tracking-[0.4em] text-slate-500">Invoice Items</p>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-900 text-white">
                            <tr class="text-left text-xs uppercase tracking-[0.4em]">
                                <th class="px-3 py-3 font-semibold">Item</th>
                                <th class="px-3 py-3 font-semibold">Qty</th>
                                <th class="px-3 py-3 font-semibold">Rate</th>
                                <th class="px-3 py-3 font-semibold">GST%</th>
                                <th class="px-3 py-3 text-right font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($invoice->items as $item)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-3 py-3 font-semibold text-slate-900">{{ $item->name }}</td>
                                    <td class="px-3 py-3">{{ number_format($item->qty_billed, 2) }}</td>
                                    <td class="px-3 py-3">{{ $currencySymbol }}{{ number_format($item->rate, 2) }}</td>
                                    <td class="px-3 py-3">{{ number_format($item->gst_percent, 2) }}%</td>
                                    <td class="px-3 py-3 text-right font-semibold text-slate-900">{{ $currencySymbol }}{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pricing Breakdown Section -->
            <div class="space-y-4 px-6 pb-8 flex justify-end">
                <div class="w-full max-w-sm space-y-4 rounded-[24px] border border-slate-100 bg-slate-50 p-6 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-500">Pricing Breakdown</p>
                    <div class="space-y-2 text-sm text-slate-700 divide-y divide-slate-200">
                        <div class="flex justify-between pb-2">
                            <span>Subtotal</span>
                            <span class="font-medium">{{ $currencySymbol }}{{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span>Discount</span>
                            <span class="font-medium">{{ $invoice->discount_type === 'percent' ? number_format($invoice->discount_value, 2).'%' : $currencySymbol . number_format($invoice->discount_value, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span>Discount Amount</span>
                            <span class="font-medium">{{ $currencySymbol }}{{ number_format($invoice->discount_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span>CGST</span>
                            <span class="font-medium">{{ $currencySymbol }}{{ number_format($invoice->cgst, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span>SGST</span>
                            <span class="font-medium">{{ $currencySymbol }}{{ number_format($invoice->sgst, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span>IGST</span>
                            <span class="font-medium">{{ $currencySymbol }}{{ number_format($invoice->igst, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span>Round Off</span>
                            <span class="font-medium">{{ $currencySymbol }}{{ number_format($invoice->round_off, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Sections -->
            <div class="grid gap-6 px-6 pb-8 lg:grid-cols-3 border-t border-slate-100">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Reference & Meta</p>
                    <p class="mt-2 text-sm text-slate-700"><strong>Invoice Type:</strong> {{ ucfirst($invoice->invoice_type) }}</p>
                    <p class="text-sm text-slate-700"><strong>PO #:</strong> {{ $invoice->po_number ?: 'N/A' }}</p>
                    <p class="text-sm text-slate-700"><strong>Ref #:</strong> {{ $invoice->reference_no ?: 'N/A' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Bank Details</p>
                    <p class="mt-2 text-sm text-slate-700"><strong>Bank:</strong> {{ $invoice->bank_name ?: 'N/A' }}</p>
                    <p class="text-sm text-slate-700"><strong>Account:</strong> {{ $invoice->account_number ?: 'N/A' }}</p>
                    <p class="text-sm text-slate-700"><strong>IFSC:</strong> {{ $invoice->ifsc_code ?: 'N/A' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Notes</p>
                    <p class="mt-2 text-sm text-slate-700">
                        @if($invoice->notes)
                            {{ $invoice->notes }}
                        @else
                            —
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @if($viewOnly)
        @else
            <!-- Payment Recording Section -->
            <div class="rounded-[28px] border border-slate-200 bg-white shadow-xl p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Record Payment</h2>
                        <p class="text-sm text-slate-500 mt-1">Use this after Razorpay capture or offline collections.</p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-white shadow-lg hover:bg-emerald-600 transition disabled:cursor-not-allowed disabled:opacity-70"
                        :disabled="isPaid || isQuickPaying"
                        @click="markAsPaid"
                    >
                        <svg x-show="isQuickPaying" class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" opacity="0.25"></circle>
                            <path d="M22 12a10 10 0 0 0-10-10"></path>
                        </svg>
                        <span x-text="isPaid ? 'Already Paid' : (isQuickPaying ? 'Processing...' : 'Mark as Paid')"></span>
                    </button>
                </div>

                <form action="{{ route('invoices.markPaidManual', $invoice) }}" method="POST" class="mt-6 space-y-4" @submit.prevent="recordManualPayment" data-prevent-double-submit="true">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-3">
                        <label class="text-xs font-semibold text-slate-500">
                            Amount (<span x-text="currencySymbol"></span>)
                            <input
                                type="number"
                                step="0.01"
                                name="amount"
                                x-model="manualAmount"
                                class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                        </label>
                        <label class="text-xs font-semibold text-slate-500">
                            Razorpay Payment ID
                            <input
                                type="text"
                                name="payment_id"
                                x-model="paymentId"
                                class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                        </label>
                        <label class="text-xs font-semibold text-slate-500">
                            Razorpay Order ID
                            <input
                                type="text"
                                name="order_id"
                                x-model="orderId"
                                class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                        </label>
                    </div>
                    <div class="flex flex-wrap gap-3 items-center">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-white shadow-lg hover:bg-slate-800 transition disabled:cursor-not-allowed disabled:opacity-70"
                            :disabled="isSubmitting || isPaid"
                        >
                            <svg x-show="isSubmitting" class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" opacity="0.25"></circle>
                                <path d="M22 12a10 10 0 0 0-10-10"></path>
                            </svg>
                            <span x-text="isSubmitting ? 'Recording...' : (isPaid ? 'Already Paid' : 'Record Payment')"></span>
                        </button>
                        <p class="text-xs text-slate-500">IDs auto-generate as traceable manual codes if left empty.</p>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <script>
        function invoicePaymentManager(config) {
            return {
                markPaidUrl: config.markPaidUrl,
                amountDue: Number(config.amountDue || 0),
                amountPaid: Number(config.amountPaid || 0),
                grandTotal: Number(config.grandTotal || 0),
                paymentStatus: String(config.paymentStatus || 'unpaid'),
                invoiceStatus: String(config.invoiceStatus || 'sent'),
                isOverdue: Boolean(config.isOverdue || false),
                currencySymbol: config.currencySymbol || 'Rs ',
                manualAmount: Number(config.amountDue || 0).toFixed(2),
                paymentId: '',
                orderId: config.defaultOrderId || '',
                isSubmitting: false,
                isQuickPaying: false,
                get isPaid() {
                    return this.paymentStatus === 'paid' || this.amountDue <= 0;
                },
                formatCurrency(value) {
                    return `${this.currencySymbol}${Number(value || 0).toFixed(2)}`;
                },
                paymentBadgeClass() {
                    if (this.paymentStatus === 'paid') {
                        return 'bg-emerald-100 text-emerald-700';
                    }

                    if (this.paymentStatus === 'partial') {
                        return 'bg-amber-100 text-amber-700';
                    }

                    return 'bg-rose-100 text-rose-700';
                },
                secondaryStatusLabel() {
                    if (this.paymentStatus === 'paid') {
                        return '';
                    }

                    if (this.invoiceStatus === 'draft') {
                        return 'DRAFT';
                    }

                    if (this.invoiceStatus === 'cancelled') {
                        return 'CANCELLED';
                    }

                    if (this.isOverdue || this.invoiceStatus === 'overdue') {
                        return 'OVERDUE';
                    }

                    return 'SENT';
                },
                async markAsPaid() {
                    if (this.isPaid || this.isQuickPaying) {
                        return;
                    }

                    const result = await Swal.fire({
                        title: 'Mark invoice as paid?',
                        text: `This will apply the remaining ${this.formatCurrency(this.amountDue)}.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, mark as paid',
                        cancelButtonText: 'Cancel',
                    });

                    if (!result.isConfirmed) {
                        return;
                    }

                    this.isQuickPaying = true;

                    try {
                        await this.submitPayment({});
                        this.toast('success', 'Invoice marked as paid.');
                    } catch (error) {
                        this.toast('error', this.extractErrorMessage(error));
                    } finally {
                        this.isQuickPaying = false;
                    }
                },
                async recordManualPayment() {
                    if (this.isSubmitting || this.isPaid) {
                        return;
                    }

                    const amount = Number(this.manualAmount);
                    if (!(amount > 0)) {
                        this.toast('error', 'Enter a valid payment amount.');
                        return;
                    }

                    const confirmation = await Swal.fire({
                        title: 'Record payment?',
                        text: `Apply ${this.formatCurrency(amount)} to Invoice ${this.invoiceNumber}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Record payment',
                        cancelButtonText: 'Cancel',
                    });

                    if (!confirmation.isConfirmed) {
                        return;
                    }

                    const payload = { amount };
                    if (this.paymentId.trim() !== '') {
                        payload.payment_id = this.paymentId.trim();
                    }
                    if (this.orderId.trim() !== '') {
                        payload.order_id = this.orderId.trim();
                    }

                    this.isSubmitting = true;

                    try {
                        await this.submitPayment(payload);
                        this.paymentId = '';
                        this.toast('success', 'Payment recorded.');
                    } catch (error) {
                        this.toast('error', this.extractErrorMessage(error));
                    } finally {
                        this.isSubmitting = false;
                    }
                },
                async submitPayment(payload) {
                    const response = await window.axios.post(this.markPaidUrl, payload, {
                        headers: {
                            Accept: 'application/json',
                        },
                    });

                    this.syncInvoiceState(response && response.data ? response.data.invoice : null);
                },
                syncInvoiceState(invoice) {
                    if (!invoice) {
                        return;
                    }

                    this.amountDue = Number(invoice.amount_due || 0);
                    this.amountPaid = Number(invoice.amount_paid || 0);
                    this.grandTotal = Number(invoice.grand_total || this.grandTotal || 0);
                    this.paymentStatus = String(invoice.payment_status || this.paymentStatus);
                    this.invoiceStatus = String(invoice.status || this.invoiceStatus);
                    this.isOverdue = this.invoiceStatus === 'overdue';
                    this.manualAmount = Number(this.amountDue).toFixed(2);
                },
                extractErrorMessage(error) {
                    const data = error && error.response ? error.response.data : {};
                    if (typeof data.message === 'string' && data.message.trim() !== '') {
                        return data.message;
                    }

                    if (data.errors && typeof data.errors === 'object') {
                        const first = Object.values(data.errors)[0];
                        if (Array.isArray(first) && first[0]) {
                            return first[0];
                        }
                    }

                    return 'Unable to record payment at the moment.';
                },
                toast(icon, title) {
                    if (window.notifyToast) {
                        window.notifyToast({ icon, title });
                        return;
                    }

                    Swal.fire({
                        icon,
                        title,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true,
                    });
                },
            };
        }
        document.addEventListener('DOMContentLoaded', function () {
            const sendForms = document.querySelectorAll('.js-send-invoice');
            sendForms.forEach((form) => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const invoiceNumber = form.dataset.invoiceNumber || 'invoice';
                    const clientName = form.dataset.clientName || 'client';
                    Swal.fire({
                        title: `Send ${invoiceNumber}?`,
                        text: `Email ${invoiceNumber} to ${clientName}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Send invoice',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            const downloadLinks = document.querySelectorAll('.js-download-pdf');
            downloadLinks.forEach((link) => {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    const invoiceNumber = link.dataset.invoiceNumber || 'invoice';
                    Swal.fire({
                        title: `Download ${invoiceNumber}?`,
                        text: 'The PDF will download immediately after you confirm.',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Download',
                        cancelButtonText: 'Cancel',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = link.href;
                        }
                    });
                });
            });
        });
    </script>
@endsection
