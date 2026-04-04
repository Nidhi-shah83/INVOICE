@extends('layouts.app')

@section('page-title', 'New Invoice')

@section('content')
    @php
        $prefill = is_array($prefill ?? null) ? $prefill : [];
        $draftLoaded = ! empty($prefill);

        $prefillItems = collect(data_get($prefill, 'items', []))
            ->map(fn ($item) => [
                'name' => (string) data_get($item, 'name', ''),
                'quantity' => (float) data_get($item, 'quantity', data_get($item, 'qty', 1)),
                'rate' => (float) data_get($item, 'rate', 0),
                'amount' => (float) data_get($item, 'amount', (data_get($item, 'quantity', data_get($item, 'qty', 1)) * data_get($item, 'rate', 0))),
            ])
            ->values()
            ->all();

        $initialItems = old('items', $prefillItems ?: [['name' => '', 'quantity' => 1, 'rate' => 0, 'amount' => 0]]);

        $vendorName = old('vendor_name', data_get($prefill, 'vendor_name', data_get($prefill, 'client_name', data_get($prefill, 'supplier_name', ''))));
        $gstin = old('gstin', data_get($prefill, 'gstin'));
        $invoiceNumber = old('invoice_number', data_get($prefill, 'invoice_number', data_get($prefill, 'invoice_no')));
        $date = old('date', data_get($prefill, 'date', data_get($prefill, 'invoice_date', now()->toDateString())));
        $totalAmount = old('total_amount', data_get($prefill, 'total_amount', data_get($prefill, 'total', 0)));
    @endphp

    <div class="space-y-6">
        @if ($draftLoaded)
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                AI Draft Loaded &mdash; Review before saving
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <p class="font-semibold">Please fix the highlighted fields.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('invoices.store') }}" id="invoice-create-form" class="space-y-6" x-data="invoiceItemsForm(@js($initialItems))">
            @csrf

            <div class="grid gap-4 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="vendor_name">Vendor Name <span class="text-rose-500">*</span></label>
                    <input
                        id="vendor_name"
                        name="vendor_name"
                        type="text"
                        value="{{ $vendorName }}"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="gstin">GSTIN</label>
                    <input
                        id="gstin"
                        name="gstin"
                        type="text"
                        value="{{ $gstin }}"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm uppercase focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="invoice_number">Invoice Number</label>
                    <input
                        id="invoice_number"
                        name="invoice_number"
                        type="text"
                        value="{{ $invoiceNumber }}"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="date">Date <span class="text-rose-500">*</span></label>
                    <input
                        id="date"
                        name="date"
                        type="date"
                        value="{{ $date }}"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="total_amount">Total Amount</label>
                    <input
                        id="total_amount"
                        name="total_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        value="{{ $totalAmount }}"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                </div>

                <div class="lg:col-span-3">
                    <label class="block text-sm font-semibold text-slate-700" for="notes">Notes</label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="2"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-900 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Item</th>
                                <th class="px-4 py-3 text-left font-semibold">Quantity</th>
                                <th class="px-4 py-3 text-left font-semibold">Rate</th>
                                <th class="px-4 py-3 text-right font-semibold">Amount</th>
                                <th class="px-4 py-3 text-center font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <template x-for="(item, index) in items" :key="`item-${index}`">
                                <tr>
                                    <td class="px-4 py-3">
                                        <input
                                            type="text"
                                            :name="`items[${index}][name]`"
                                            x-model="item.name"
                                            class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                        >
                                    </td>
                                    <td class="px-4 py-3">
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            :name="`items[${index}][quantity]`"
                                            x-model.number="item.quantity"
                                            @input="syncAmount(index)"
                                            class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                        >
                                    </td>
                                    <td class="px-4 py-3">
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            :name="`items[${index}][rate]`"
                                            x-model.number="item.rate"
                                            @input="syncAmount(index)"
                                            class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                        >
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            :name="`items[${index}][amount]`"
                                            x-model.number="item.amount"
                                            @input="item.amount = Number(item.amount || 0)"
                                            class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-right text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                        >
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button
                                            type="button"
                                            @click.prevent="removeItem(index)"
                                            class="text-xs text-rose-600 hover:text-rose-500"
                                        >
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-3">
                    <button
                        type="button"
                        @click.prevent="addItem()"
                        class="inline-flex items-center gap-2 rounded-full border border-dashed border-slate-400 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-600"
                    >
                        + item
                    </button>
                    <p class="text-sm text-slate-500">Items Total: <span class="font-semibold text-slate-900" x-text="formatTotal()"></span></p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button
                    type="button"
                    data-save-action="draft"
                    data-save-label="Save Draft"
                    class="js-save-invoice inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-slate-800 transition"
                >
                    Save Draft
                </button>
                <button
                    type="button"
                    data-save-action="final"
                    data-save-label="Save Final"
                    class="js-save-invoice inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-600 transition"
                >
                    Save Final
                </button>
            </div>
        </form>
    </div>

    <script>
        function invoiceItemsForm(initialItems) {
            const sanitized = (Array.isArray(initialItems) && initialItems.length ? initialItems : [{
                name: '',
                quantity: 1,
                rate: 0,
                amount: 0
            }]).map((item) => ({
                name: item.name ?? '',
                quantity: Number(item.quantity ?? 1),
                rate: Number(item.rate ?? 0),
                amount: Number(item.amount ?? (Number(item.quantity ?? 1) * Number(item.rate ?? 0))),
            }));

            return {
                items: sanitized,
                addItem() {
                    this.items.push({
                        name: '',
                        quantity: 1,
                        rate: 0,
                        amount: 0,
                    });
                },
                removeItem(index) {
                    this.items.splice(index, 1);

                    if (this.items.length === 0) {
                        this.addItem();
                    }
                },
                syncAmount(index) {
                    const item = this.items[index];
                    item.amount = Number((Number(item.quantity || 0) * Number(item.rate || 0)).toFixed(2));
                },
                formatTotal() {
                    const total = this.items.reduce((sum, item) => sum + Number(item.amount || 0), 0);
                    return total.toFixed(2);
                }
            };
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('invoice-create-form');
            if (!form) return;

            document.querySelectorAll('.js-save-invoice').forEach(button => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    handleInvoiceSave(form, button);
                });
            });
        });

        function handleInvoiceSave(form, button) {
            const action = button.dataset.saveAction;
            const label = button.dataset.saveLabel ?? 'Save';

            if (!action) {
                return;
            }

            if (!window.Swal) {
                submitInvoiceForm(form, action);
                return;
            }

            const message = action === 'final'
                ? 'Submit this invoice as final?'
                : 'Save a draft to revisit later?';

            Swal.fire({
                title: label,
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: label,
                cancelButtonText: 'Cancel',
            }).then(result => {
                if (result.isConfirmed) {
                    submitInvoiceForm(form, action);
                }
            });
        }

        function submitInvoiceForm(form, action) {
            let input = form.querySelector('input[name="action"][type="hidden"]');
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'action';
                form.appendChild(input);
            }

            input.value = action;
            form.submit();
        }
    </script>
@endsection
