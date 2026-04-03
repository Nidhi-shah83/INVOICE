@extends('layouts.app')

@section('page-title', $order->order_number)

@section('content')
    <div
        class="space-y-6"
        x-data="orderStatusManager({
            initialStatus: @js($order->status),
            updateUrl: @js(route('orders.updateStatus', $order)),
        })"
    >
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
                <form class="flex flex-wrap gap-2" method="POST" action="{{ route('orders.updateStatus', $order) }}" @submit.prevent="confirmAndUpdate">
                    @csrf
                    <select
                        name="status"
                        x-model="selectedStatus"
                        :disabled="isSubmitting"
                        class="rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 disabled:opacity-60"
                    >
                        @foreach(['confirmed','in_progress','partially_billed','fulfilled','fully_billed','cancelled'] as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white shadow-lg hover:bg-slate-800 transition disabled:cursor-not-allowed disabled:opacity-70"
                        :disabled="isSubmitting || selectedStatus === currentStatus"
                    >
                        <svg x-show="isSubmitting" class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" opacity="0.25"></circle>
                            <path d="M22 12a10 10 0 0 0-10-10"></path>
                        </svg>
                        <span x-text="isSubmitting ? 'Updating...' : 'Update status'"></span>
                    </button>
                </form>

                @if($order->remaining_amount > 0)
                    <div class="flex flex-wrap gap-2">
                        <button
                            onclick="toggleConvertOptions()"
                            class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2 text-xs font-semibold text-white shadow-lg hover:bg-emerald-600 transition"
                        >
                            Convert to Invoice
                        </button>
                    </div>

                    <div id="convert-options" class="hidden mt-4 p-4 bg-slate-50 rounded-2xl">
                        <h4 class="text-sm font-semibold text-slate-900 mb-3">Choose Conversion Method</h4>

                        <div class="space-y-3">
                            <div>
                                <h5 class="text-xs font-semibold text-slate-700 mb-2">Option 1: Convert All Remaining Items</h5>
                                <form method="POST" action="{{ route('orders.createInvoice', $order) }}" onsubmit="return confirm('Create invoice for all remaining items (₹{{ number_format($order->remaining_amount, 2) }})?')">
                                    @csrf
                                    @foreach($order->items as $item)
                                        @if($item->qty_remaining > 0)
                                            <input type="hidden" name="items[{{ $loop->index }}][order_item_id]" value="{{ $item->id }}">
                                            <input type="hidden" name="items[{{ $loop->index }}][qty]" value="{{ $item->qty_remaining }}">
                                        @endif
                                    @endforeach
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 items-end">
                                        <label class="flex flex-col text-xs text-slate-600">
                                            <span>Issue Date</span>
                                            <input
                                                type="date"
                                                name="issue_date"
                                                value="{{ now()->format('Y-m-d') }}"
                                                class="mt-1 rounded-2xl border border-slate-200 px-3 py-1 text-sm"
                                                required
                                            >
                                        </label>

                                        <label class="flex flex-col text-xs text-slate-600">
                                            <span>Due Date</span>
                                            <input
                                                type="date"
                                                name="due_date"
                                                value="{{ now()->addDays(15)->format('Y-m-d') }}"
                                                class="mt-1 rounded-2xl border border-slate-200 px-3 py-1 text-sm"
                                                required
                                            >
                                        </label>
                                    </div>

                                    <div class="mt-3">
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-600">
                                            Create Full Invoice
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div>
                                <h5 class="text-xs font-semibold text-slate-700 mb-2">Option 2: Select Specific Items</h5>
                                <button
                                    onclick="togglePartialBilling()"
                                    class="inline-flex items-center gap-2 rounded-full bg-slate-500 px-3 py-1 text-xs font-semibold text-white hover:bg-slate-600"
                                >
                                    Select Items Below
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('orders.destroy', $order) }}">
                    @csrf
                    @method('DELETE')
                    <button class="text-xs text-rose-600 hover:text-rose-400">Delete order</button>
                </form>
            </div>
        </div>

        @if($order->quote)
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400 mb-3">Quote Details</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-xs uppercase tracking-[0.3em] text-slate-400">Quote #</span>
                        <p class="font-semibold text-slate-900">{{ $order->quote->quote_number }}</p>
                    </div>
                    <div>
                        <span class="text-xs uppercase tracking-[0.3em] text-slate-400">Issue Date</span>
                        <p class="font-semibold text-slate-900">{{ $order->quote->issue_date?->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <span class="text-xs uppercase tracking-[0.3em] text-slate-400">Validity Date</span>
                        <p class="font-semibold text-slate-900">{{ $order->quote->validity_date?->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <span class="text-xs uppercase tracking-[0.3em] text-slate-400">Total</span>
                        <p class="font-semibold text-slate-900">₹{{ number_format($order->quote->grand_total, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-xs uppercase tracking-[0.3em] text-slate-400">Payment Terms</span>
                        <p class="font-semibold text-slate-900">{{ $order->quote->payment_terms ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-xs uppercase tracking-[0.3em] text-slate-400">Salesperson</span>
                        <p class="font-semibold text-slate-900">{{ $order->quote->salesperson ?? 'N/A' }}</p>
                    </div>
                </div>
                @if($order->quote->notes)
                    <div class="mt-4">
                        <span class="text-xs uppercase tracking-[0.3em] text-slate-400">Notes</span>
                        <p class="text-sm text-slate-600 mt-1">{{ $order->quote->notes }}</p>
                    </div>
                @endif
            </div>
        @endif

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
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900">Partial Billing</h3>
                <button
                    onclick="togglePartialBilling()"
                    class="text-sm text-slate-600 hover:text-slate-900"
                >
                    <span id="partial-toggle-text">Show</span> Options
                </button>
            </div>
            <div id="partial-billing-container" class="hidden">
                @livewire('partial-billing-form', ['order' => $order])
            </div>
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
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $invoice->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($invoice->payment_status === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                                            {{ ucfirst($invoice->payment_status) }}
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

    <script>
        function orderStatusManager({ initialStatus, updateUrl }) {
            return {
                currentStatus: initialStatus,
                selectedStatus: initialStatus,
                updateUrl,
                isSubmitting: false,
                async confirmAndUpdate() {
                    if (this.isSubmitting || this.selectedStatus === this.currentStatus) {
                        return;
                    }

                    const readable = this.selectedStatus.replace(/_/g, ' ');
                    const result = await Swal.fire({
                        title: 'Change order status?',
                        text: `Set this order to "${readable}"?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, update it',
                        cancelButtonText: 'Cancel',
                    });

                    if (!result.isConfirmed) {
                        this.selectedStatus = this.currentStatus;
                        return;
                    }

                    this.isSubmitting = true;

                    try {
                        await window.axios.post(this.updateUrl, {
                            status: this.selectedStatus,
                        }, {
                            headers: {
                                Accept: 'application/json',
                            },
                        });

                        this.currentStatus = this.selectedStatus;
                        this.toast('success', 'Order status updated.');
                    } catch (error) {
                        this.selectedStatus = this.currentStatus;
                        const message = error?.response?.data?.message || 'Unable to update order status.';
                        this.toast('error', message);
                    } finally {
                        this.isSubmitting = false;
                    }
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

        function toggleConvertOptions() {
            const options = document.getElementById('convert-options');
            options.classList.toggle('hidden');
        }

        function togglePartialBilling() {
            const container = document.getElementById('partial-billing-container');
            const toggleText = document.getElementById('partial-toggle-text');
            const isHidden = container.classList.contains('hidden');

            if (isHidden) {
                container.classList.remove('hidden');
                toggleText.textContent = 'Hide';
            } else {
                container.classList.add('hidden');
                toggleText.textContent = 'Show';
            }
        }
    </script>
@endsection
