@extends('layouts.app')

@section('page-title', 'Invoice '.$invoice->invoice_number)

@section('primary-action')
    <div class="flex items-center gap-2">
        <a href="{{ route('invoices.download', $invoice) }}" class="inline-flex items-center gap-2 rounded-full border border-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-900">
            Download PDF
        </a>
        <form action="{{ route('invoices.send', $invoice) }}" method="POST" data-prevent-double-submit="true">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-white" data-loading-text="Sending...">
                Send Invoice
            </button>
        </form>
    </div>
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
            currencySymbol: @js(config('invoice.currency_symbol', 'Rs ')),
            defaultOrderId: @js((string) ($invoice->order?->order_number ?? $invoice->razorpay_order_id ?? $invoice->invoice_number)),
        })"
    >
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Invoice</p>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ $invoice->invoice_number }}</h1>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold tracking-[0.2em]" :class="paymentBadgeClass()" x-text="paymentStatus.toUpperCase()"></span>
                    <p x-show="secondaryStatusLabel() !== ''" x-text="secondaryStatusLabel()" class="text-[11px] uppercase tracking-[0.2em] text-slate-400"></p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-500">Client</h3>
                    <div class="mt-2 flex items-center gap-3">
                        <div>
                            <p class="text-base font-semibold text-slate-900">{{ $invoice->client->name }}</p>
                            <p class="text-sm text-slate-500">{{ $invoice->client->email }}</p>
                            <p class="text-sm text-slate-500">{{ $invoice->client->phone ?? 'N/A' }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold tracking-[0.3em] text-slate-600">
                            {{ strtoupper($invoice->client->gst_type ?? 'N/A') }}
                        </span>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-slate-500">Dates & Terms</h3>
                    <div class="mt-2 space-y-1 text-sm text-slate-600">
                        <p>Issue: {{ $invoice->issue_date?->format('d M, Y') ?? 'N/A' }}</p>
                        <p>Due: {{ $invoice->due_date?->format('d M, Y') ?? 'N/A' }}</p>
                        <p>Payment terms: {{ $invoice->payment_terms ?? 'As agreed' }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-slate-500">Totals</h3>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Grand total</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ $invoice->formatted_grand_total }}</p>
                        <div class="mt-3 space-y-1 text-sm">
                            <p>Paid: <span x-text="formatCurrency(amountPaid)"></span></p>
                            <p>Due: <span class="font-semibold text-rose-600" x-text="formatCurrency(amountDue)"></span></p>
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
                    <span class="font-semibold text-slate-900">{{ config('invoice.currency_symbol', 'Rs ') }}{{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Discount</span>
                    <span class="font-semibold text-slate-900">
                        {{ $invoice->discount_type === 'percent' ? number_format($invoice->discount_value, 2).'%' : config('invoice.currency_symbol', 'Rs ').number_format($invoice->discount_value, 2) }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Discount amount</span>
                    <span class="font-semibold text-slate-900">{{ config('invoice.currency_symbol', 'Rs ') }}{{ number_format($invoice->discount_amount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Taxable amount</span>
                    <span class="font-semibold text-slate-900">{{ config('invoice.currency_symbol', 'Rs ') }}{{ number_format(max(0, $invoice->subtotal - $invoice->discount_amount), 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>CGST</span>
                    <span>{{ config('invoice.currency_symbol', 'Rs ') }}{{ number_format($invoice->cgst, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>SGST</span>
                    <span>{{ config('invoice.currency_symbol', 'Rs ') }}{{ number_format($invoice->sgst, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>IGST</span>
                    <span>{{ config('invoice.currency_symbol', 'Rs ') }}{{ number_format($invoice->igst, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Round off</span>
                    <span>{{ config('invoice.currency_symbol', 'Rs ') }}{{ number_format($invoice->round_off, 2) }}</span>
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
                                <td class="px-4 py-3 text-right">{{ config('invoice.currency_symbol', 'Rs ') }}{{ number_format($item->rate, 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item->gst_percent, 2) }}%</td>
                                <td class="px-4 py-3 text-right">{{ config('invoice.currency_symbol', 'Rs ') }}{{ number_format($item->amount, 2) }}</td>
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
                    <p>Due date: {{ $invoice->due_date?->format('d M, Y') ?? 'N/A' }}</p>
                    <p>Currency: {{ $invoice->currency }}</p>
                    <p>Payment link: @if($invoice->payment_link)<a href="{{ $invoice->payment_link }}" target="_blank" class="text-emerald-600 hover:underline">Pay online</a>@else N/A @endif</p>
                </div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                <h2 class="text-sm font-semibold text-slate-500">Reference & meta</h2>
                <div class="space-y-1 text-sm text-slate-600">
                    <p>Invoice type: {{ ucfirst($invoice->invoice_type) }}</p>
                    <p>PO number: {{ $invoice->po_number ?: 'N/A' }}</p>
                    <p>Reference #: {{ $invoice->reference_no ?: 'N/A' }}</p>
                </div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                <h2 class="text-sm font-semibold text-slate-500">Bank details</h2>
                <div class="space-y-1 text-sm text-slate-600">
                    <p>Bank: {{ $invoice->bank_name ?: 'N/A' }}</p>
                    <p>Account #: {{ $invoice->account_number ?: 'N/A' }}</p>
                    <p>IFSC: {{ $invoice->ifsc_code ?: 'N/A' }}</p>
                    <p>UPI ID: {{ $invoice->upi_id ?: 'N/A' }}</p>
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
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">Manually record payment</h2>
                    <p class="text-xs text-slate-500">Use this after Razorpay capture or offline collections.</p>
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

            <form action="{{ route('invoices.markPaidManual', $invoice) }}" method="POST" class="mt-4 space-y-4" @submit.prevent="recordManualPayment" data-prevent-double-submit="true">
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
                <div class="flex flex-wrap gap-3">
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
                    <p class="text-xs text-slate-500">If payment/order IDs are empty, the system will generate traceable manual IDs.</p>
                </div>
            </form>
        </div>
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
    </script>
@endsection
