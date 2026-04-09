@extends('layouts.app')

@section('page-title', $order->order_number)

@section('primary-action')
    <div class="flex flex-wrap gap-3">
        <form
            method="POST"
            action="{{ route('orders.sendPdf', $order) }}"
            class="send-pdf-form"
        >
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-slate-800 transition">
                Send PDF
            </button>
        </form>
        <a
            href="{{ route('orders.pdf', $order) }}?download=1"
            class="inline-flex items-center gap-2 rounded-full border border-slate-900 px-5 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-900 hover:text-white transition download-pdf-link"
        >
            Download PDF
        </a>
    </div>
@endsection

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
                        @foreach(['pending','accepted','confirmed','in_progress','partially_billed','fulfilled','fully_billed','cancelled'] as $status)
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
                            onclick="openConversionAlert()"
                            class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2 text-xs font-semibold text-white shadow-lg hover:bg-emerald-600 transition"
                        >
                            Convert to Invoice
                        </button>
                    </div>

                    <div id="convert-options" class="hidden mt-4 p-4 bg-slate-50 rounded-2xl">
                        <h4 class="text-sm font-semibold text-slate-900 mb-3">Choose Conversion Method</h4>

                        <div class="space-y-3">
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

                    <form
                        method="POST"
                        action="{{ route('orders.createInvoice', $order) }}"
                        id="convert-all-swal-form"
                        class="hidden"
                    >
                        @csrf
                        @foreach($order->items as $item)
                            @if($item->qty_remaining > 0)
                                <input type="hidden" name="items[{{ $loop->index }}][order_item_id]" value="{{ $item->id }}">
                                <input type="hidden" name="items[{{ $loop->index }}][qty]" value="{{ $item->qty_remaining }}">
                            @endif
                        @endforeach
                        <input type="hidden" name="issue_date" id="ssw-issue-date" value="{{ now()->format('Y-m-d') }}">
                        <input type="hidden" name="due_date" id="ssw-due-date" value="{{ now()->addDays(15)->format('Y-m-d') }}">
                    </form>
                @endif

                <form
                    method="POST"
                    action="{{ route('orders.destroy', $order) }}"
                    class="delete-order-form"
                >
                    @csrf
                    @method('DELETE')
                    <button class="text-xs text-rose-600 hover:text-rose-400">Delete order</button>
                </form>
            </div>
            @if($order->quote)
                <div class="space-y-3">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Quote Details</p>
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
                        <div>
                            <span class="text-xs uppercase tracking-[0.3em] text-slate-400">Notes</span>
                            <p class="text-sm text-slate-600 mt-1">{{ $order->quote->notes }}</p>
                        </div>
                    @endif
                </div>
            @endif

            <div class="space-y-3">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Order Items</p>
                <div class="overflow-x-auto">
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
                    const confirmed = window.confirmSwal
                        ? await window.confirmSwal({
                            title: 'Change order status?',
                            text: `Set this order to "${readable}"?`,
                            icon: 'question',
                            confirmButtonText: 'Yes, update it',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#111827',
                        })
                        : await Swal.fire({
                            title: 'Change order status?',
                            text: `Set this order to "${readable}"?`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, update it',
                            cancelButtonText: 'Cancel',
                        }).then((result) => result.isConfirmed);

                    if (!confirmed) {
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

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.convert-invoice-form').forEach(form => {
                form.addEventListener('submit', event => handleConvertForm(event, form));
            });

            document.querySelectorAll('.delete-order-form').forEach(form => {
                form.addEventListener('submit', event => handleDeleteForm(event, form));
            });

            document.querySelectorAll('.send-pdf-form').forEach(form => {
                form.addEventListener('submit', event => handleSendPdfForm(event, form));
            });

            document.querySelectorAll('.download-pdf-link').forEach(link => {
                link.addEventListener('click', event => handleDownloadLink(event, link));
            });
        });

        function handleConvertForm(event, form) {
            event.preventDefault();

            if (!window.confirmSwal) {
                form.submit();
                return;
            }

            window.confirmSwal({
                title: form.dataset.swalTitle || 'Convert order?',
                text: form.dataset.swalText || 'This will create an invoice for all remaining items.',
                icon: form.dataset.swalIcon || 'question',
                confirmButtonText: form.dataset.swalConfirmButton || 'Create invoice',
                cancelButtonText: form.dataset.swalCancelButton || 'Cancel',
                confirmButtonColor: form.dataset.swalConfirmColor || '#10b981',
            }).then((confirmed) => {
                if (confirmed) {
                    form.submit();
                }
            });
        }

        function handleDeleteForm(event, form) {
            event.preventDefault();

            if (!window.confirmSwal) {
                form.submit();
                return;
            }

            window.confirmSwal({
                title: form.dataset.swalTitle || 'Delete order?',
                text: form.dataset.swalText || 'This action cannot be undone.',
                icon: form.dataset.swalIcon || 'warning',
                confirmButtonText: form.dataset.swalConfirmButton || 'Yes, delete it',
                cancelButtonText: form.dataset.swalCancelButton || 'Keep it',
                confirmButtonColor: form.dataset.swalConfirmColor || '#ef4444',
            }).then((confirmed) => {
                if (confirmed) {
                    form.submit();
                }
            });
        }

        function handleSendPdfForm(event, form) {
            event.preventDefault();

            if (!window.confirmSwal) {
                form.submit();
                return;
            }

            window.confirmSwal({
                title: form.dataset.swalTitle || 'Send PDF?',
                text: form.dataset.swalText || 'The document will be emailed to the client.',
                icon: form.dataset.swalIcon || 'info',
                confirmButtonText: form.dataset.swalConfirmButton || 'Send it',
                cancelButtonText: form.dataset.swalCancelButton || 'Cancel',
                confirmButtonColor: form.dataset.swalConfirmColor || '#111827',
            }).then((confirmed) => {
                if (confirmed) {
                    form.submit();
                }
            });
        }

        function handleDownloadLink(event, link) {
            event.preventDefault();

            if (!window.confirmSwal) {
                window.location.href = link.href;
                return;
            }

            window.confirmSwal({
                title: link.dataset.swalTitle || 'Download PDF?',
                text: link.dataset.swalText || 'The file will download immediately.',
                icon: link.dataset.swalIcon || 'question',
                confirmButtonText: link.dataset.swalConfirmButton || 'Download',
                cancelButtonText: link.dataset.swalCancelButton || 'Cancel',
                confirmButtonColor: link.dataset.swalConfirmColor || '#111827',
            }).then((confirmed) => {
                if (confirmed) {
                    window.location.href = link.href;
                }
            });
        }

        function openConversionAlert() {
            if (!window.confirmSwal) {
                document.getElementById('convert-all-swal-form')?.submit();
                return;
            }

            const issueDefault = '{{ now()->format('Y-m-d') }}';
            const dueDefault = '{{ now()->addDays(15)->format('Y-m-d') }}';
            const html = `
                <div class="space-y-2 text-left">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400 mb-1">Option 1</p>
                        <p class="text-sm font-semibold text-slate-900">Convert All Remaining Items</p>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label class="text-xs text-slate-600">
                            <span>Issue Date</span>
                            <input id="swal-issue-date" type="date" class="swal2-input" value="${issueDefault}">
                        </label>
                        <label class="text-xs text-slate-600">
                            <span>Due Date</span>
                            <input id="swal-due-date" type="date" class="swal2-input" value="${dueDefault}">
                        </label>
                    </div>
                    <hr class="border-t border-slate-200 my-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400 mb-1">Option 2</p>
                        <p class="text-sm font-semibold text-slate-900">Select Specific Items</p>
                        <p class="text-xs text-slate-500 mt-1">Choose which items to bill using the partial billing table below.</p>
                    </div>
                </div>
            `;

            const fireSwal = typeof window.swalFire === 'function'
                ? window.swalFire
                : Swal.fire.bind(Swal);

            fireSwal({
                title: 'Choose Conversion Method',
                html,
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: 'Create Full Invoice',
                denyButtonText: 'Select Specific Items',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusConfirm: false,
                didOpen: () => {
                    const issueInput = Swal.getPopup().querySelector('#swal-issue-date');
                    issueInput?.focus();
                },
                preConfirm: () => {
                    const issueInput = Swal.getPopup().querySelector('#swal-issue-date');
                    const dueInput = Swal.getPopup().querySelector('#swal-due-date');

                    if (!issueInput?.value || !dueInput?.value) {
                        Swal.showValidationMessage('Issue and due dates are required.');
                        return false;
                    }

                    return {
                        issue_date: issueInput.value,
                        due_date: dueInput.value,
                    };
                },
            }).then(result => {
                const form = document.getElementById('convert-all-swal-form');
                if (!form) return;

                if (result.isConfirmed && result.value) {
                    document.getElementById('ssw-issue-date').value = result.value.issue_date;
                    document.getElementById('ssw-due-date').value = result.value.due_date;
                    form.submit();
                    return;
                }

                if (result.isDenied) {
                    toggleConvertOptions();
                }
            });
        }

        function formatCurrency(value) {
            return `₹${value.toFixed(2)}`;
        }
    </script>
@endsection
