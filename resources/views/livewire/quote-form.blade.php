<div class="mx-auto max-w-7xl space-y-6 px-6 py-6 overflow-visible">
@php
    $fieldClass = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white';
    $selectClass = $fieldClass.' pr-10';
    $cardClass = 'rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900';
    $sectionTitleClass = 'text-sm font-semibold text-slate-900 dark:text-slate-100';
    $sectionDescClass = 'text-xs text-slate-500 dark:text-slate-400';
    $primaryButtonClass = 'inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200';
    $secondaryButtonClass = 'inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800';
@endphp
<form wire:submit.prevent="saveDraft" class="space-y-6 overflow-visible">
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-slate-200 bg-white px-6 py-4 shadow-sm">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Quote Number</p>
            <p class="text-2xl font-semibold text-slate-900">{{ $quoteNumberPreview ?? 'Draft' }}</p>
        </div>
        <div class="text-right text-xs text-slate-500">
            <p>Business GSTIN: {{ setting('gstin', '') }}</p>
            <p>{{ setting('business_name', 'Invoice Pro') }}</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div>
            <label class="block text-sm font-semibold text-slate-700" for="client">Client <span class="text-rose-500">*</span></label>
            <select
                id="client"
                wire:model="client_id"
                class="mt-1 {{ $selectClass }}"
            >
                <option value="">Select client</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>
            @error('client_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700" for="issue_date">Issue date</label>
            <input
                wire:model="issue_date"
                type="date"
                id="issue_date"
                class="mt-1 {{ $fieldClass }}"
            >
            @error('issue_date') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700" for="validity_date">Validity</label>
            <input
                wire:model="validity_date"
                type="date"
                id="validity_date"
                class="mt-1 {{ $fieldClass }}"
            >
            @error('validity_date') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div
        id="client-summary"
        class="space-y-3 rounded-3xl border border-slate-200 bg-white p-4 text-sm text-slate-600 shadow-sm {{ $selectedClient ? '' : 'hidden' }}"
        data-client-summary
    >
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Client</p>
                <h3 class="text-lg font-semibold text-slate-900" data-client-name>{{ $selectedClient->name ?? '' }}</h3>
                <p class="text-xs text-slate-500" data-client-email>{{ $selectedClient->email ?? 'Email not set' }}</p>
                <p class="text-xs text-slate-500" data-client-phone>{{ $selectedClient->phone ?? '' }}</p>
            </div>
            <span
                id="client-gst-badge"
                class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]"
            >
                {{ optional($selectedClient)->gst_type === 'intra' ? 'CGST+SGST' : 'IGST' }}
            </span>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <div class="hidden" data-client-gstin-wrapper>
                <span class="text-xs uppercase tracking-[0.3em] text-slate-400">GSTIN</span>
                <p class="font-semibold text-slate-900" data-client-gstin>{{ $selectedClient->gstin ?? '' }}</p>
            </div>
            <div>
                <span class="text-xs uppercase tracking-[0.3em] text-slate-400">State</span>
                <p class="font-semibold text-slate-900" data-client-state>{{ $selectedClient->state ?? '' }}</p>
            </div>
        </div>
        <div class="hidden" data-client-address-wrapper>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Address</p>
            <p class="text-sm text-slate-500" data-client-address>{{ $selectedClient->address ?? '' }}</p>
        </div>
    </div>

        <div class="{{ $cardClass }} mb-8">
    
            <!-- Header -->
            <div class="flex items-center justify-between px-6 pt-5 pb-4">
                <div>
                    <h3 class="{{ $sectionTitleClass }}">Items</h3>
                    <p class="{{ $sectionDescClass }}">Add one or more line items for this quote.</p>
                </div>
                <button
                    type="button"
                    wire:click="addItem"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    + Add item
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto hide-scrollbar px-6 pb-5">
                <table class="min-w-full divide-y divide-slate-200 text-sm" data-quote-items-table>
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Item</th>
                            <th class="px-4 py-3 text-left font-semibold">Qty</th>
                            <th class="px-4 py-3 text-left font-semibold">Rate</th>
                            <th class="px-4 py-3 text-left font-semibold">Discount %</th>
                            <th class="px-4 py-3 text-right font-semibold">Amount</th>
                            <th class="px-4 py-3 text-center font-semibold">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($items as $index => $item)
                            <tr wire:key="item-{{ $index }}" data-quote-item>
                                <td class="px-4 py-3">
                                    <select
                                        id="item-{{ $index }}-name"
                                        data-quote-item-name
                                        wire:model="items.{{ $index }}.name"
                                        class="{{ $fieldClass }}"
                                    >
                                        <option value="">Item</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->name }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-4 py-3">
                                    <input type="number"
                                        wire:model="items.{{ $index }}.qty"
                                        data-qty-input
                                        class="{{ $fieldClass }}">
                                </td>

                                <td class="px-4 py-3">
                                    <input type="number"
                                        wire:model="items.{{ $index }}.rate"
                                        data-rate-input
                                        class="{{ $fieldClass }}">
                                </td>

                                <td class="px-4 py-3">
                                    <input type="number"
                                        wire:model="items.{{ $index }}.discount_percent"
                                        data-discount-input
                                        class="{{ $fieldClass }}">
                                    <input type="hidden"
                                        wire:model="items.{{ $index }}.gst_percent"
                                        data-gst-input
                                    >
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <span data-row-amount>
                                        {{ $currencySymbol }}{{ number_format(($item['qty'] ?? 0) * ($item['rate'] ?? 0) * (1 - (($item['discount_percent'] ?? 0) / 100)), 2) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <button
                                        type="button"
                                        wire:click="removeItem({{ $index }})"
                                        class="text-xs text-rose-600 hover:text-rose-400"
                                    >
                                        Remove
                                    </button>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="{{ $cardClass }} px-6 py-5 mb-8">
            <div class="grid gap-6 lg:grid-cols-[1.2fr,0.9fr]">

                <!-- Notes -->
                <section class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Notes</label>
                    <textarea
                        id="notes"
                        wire:model.defer="notes"
                        rows="4"
                        class="{{ $fieldClass }} min-h-[120px]"
                    ></textarea>
                </section>

                <!-- Summary -->
                <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"
                    data-quote-summary
                    data-business-state="{{ setting('state', '') }}"
                >

                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Summary</p>
                        <h3 class="text-lg font-semibold text-slate-900 mt-1">Quote totals</h3>
                    </div>

                    <p class="text-xs text-slate-500">Live numbers update as you edit line items. Only the relevant tax split appears.</p>

                    <div class="grid gap-2 text-sm text-slate-600">

                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span class="font-semibold text-slate-900" data-summary-field="subtotal">
                                {{ $currencySymbol }}{{ number_format($totals['subtotal'], 2) }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span>Taxable amount</span>
                            <span class="font-semibold text-slate-900" data-summary-field="taxable">
                                {{ $currencySymbol }}{{ number_format($totals['taxable_amount'], 2) }}
                            </span>
                        </div>

                        <div class="flex justify-between" data-summary-row="cgst">
                            <span>CGST</span>
                            <span class="font-semibold text-slate-900" data-summary-field="cgst">
                                {{ $currencySymbol }}{{ number_format($totals['cgst'], 2) }}
                            </span>
                        </div>

                        <div class="flex justify-between" data-summary-row="sgst">
                            <span>SGST</span>
                            <span class="font-semibold text-slate-900" data-summary-field="sgst">
                                {{ $currencySymbol }}{{ number_format($totals['sgst'], 2) }}
                            </span>
                        </div>

                        <div class="flex justify-between" data-summary-row="igst">
                            <span>IGST</span>
                            <span class="font-semibold text-slate-900" data-summary-field="igst">
                                {{ $currencySymbol }}{{ number_format($totals['igst'], 2) }}
                            </span>
                        </div>

                    </div>

                    <div class="rounded-2xl border border-slate-800 bg-slate-900 px-4 py-4 text-right text-2xl font-semibold text-white shadow-md">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/70">Grand total</p>
                        <p data-summary-field="grand">
                            {{ $currencySymbol }}{{ number_format($totals['grand_total'], 2) }}
                        </p>
                    </div>

                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
        <button
            type="button"
            wire:click="saveDraft"
            wire:loading.attr="disabled"
            wire:target="saveDraft"
            class="{{ $secondaryButtonClass }}"
        >
            <svg wire:loading wire:target="saveDraft" class="h-4 w-4 animate-spin text-current" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span wire:loading.remove wire:target="saveDraft">Save as Draft</span>
            <span wire:loading wire:target="saveDraft">Saving...</span>
        </button>
        <button
            type="button"
            wire:click="sendNow"
            wire:loading.attr="disabled"
            wire:target="sendNow"
            class="{{ $primaryButtonClass }}"
        >
            <svg wire:loading wire:target="sendNow" class="h-4 w-4 animate-spin text-current" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span wire:loading.remove wire:target="sendNow">Send Now</span>
            <span wire:loading wire:target="sendNow">Sending...</span>
        </button>
    </div>

@php
    $clientData = $clients->mapWithKeys(function ($client) {
        return [
            $client->id => [
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'gstin' => $client->gstin,
                'state' => $client->state,
                'address' => $client->address,
                'gst_type' => $client->gst_type,
            ],
        ];
    })->toArray();
    $quoteProductCatalog = $products->mapWithKeys(function ($product) {
        return [
            $product->name => [
                'rate' => (float) $product->rate,
                'gst_percent' => (float) $product->gst_percent,
            ],
        ];
    })->toArray();
@endphp

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const clients = @json($clientData);

            const clientSelect = document.getElementById('client');
            const summary = document.querySelector('[data-client-summary]');
            const nameEl = summary?.querySelector('[data-client-name]');
            const emailEl = summary?.querySelector('[data-client-email]');
            const phoneEl = summary?.querySelector('[data-client-phone]');
            const stateEl = summary?.querySelector('[data-client-state]');
            const addressEl = summary?.querySelector('[data-client-address]');
            const gstinEl = summary?.querySelector('[data-client-gstin]');
            const gstinWrapper = summary?.querySelector('[data-client-gstin-wrapper]');
            const addressWrapper = summary?.querySelector('[data-client-address-wrapper]');
            const badge = document.getElementById('client-gst-badge');

            const updateBadge = (type) => {
                if (!badge) return;
                badge.classList.remove('bg-emerald-100', 'text-emerald-700', 'bg-sky-100', 'text-sky-700');
                if (type === 'intra') {
                    badge.classList.add('bg-emerald-100', 'text-emerald-700');
                    badge.textContent = 'CGST+SGST';
                } else {
                    badge.classList.add('bg-sky-100', 'text-sky-700');
                    badge.textContent = 'IGST';
                }
            };

            const setText = (element, value, fallback = '') => {
                if (!element) return;
                element.textContent = value || fallback;
            };

            const toggleVisibility = (element, show) => {
                if (!element) return;
                element.classList.toggle('hidden', !show);
            };

            const fillSummary = (clientId) => {
                if (!summary) return;
                const info = clients[clientId];
                if (!info) {
                    summary.classList.add('hidden');
                    return;
                }

                setText(nameEl, info.name);
                setText(emailEl, info.email, 'Email not set');
                setText(phoneEl, info.phone);
                setText(stateEl, info.state);
                setText(addressEl, info.address);
                setText(gstinEl, info.gstin);

                updateBadge(info.gst_type);

                toggleVisibility(gstinWrapper, Boolean(info.gstin));
                toggleVisibility(addressWrapper, Boolean(info.address));
                toggleVisibility(phoneEl, Boolean(info.phone));
                summary.classList.remove('hidden');
            };

            clientSelect?.addEventListener('change', (event) => fillSummary(event.target.value));

            if (clientSelect?.value) {
                fillSummary(clientSelect.value);
            }

            const summaryCard = document.querySelector('[data-quote-summary]');
            const parseRowValue = (row) => {
                if (!row) {
                    return 0;
                }
                const raw = row.dataset.totalValue ?? '0';
                const parsed = parseFloat(raw);
                return Number.isFinite(parsed) ? parsed : 0;
            };

            const adjustTaxVisibility = () => {
                if (!summaryCard) {
                    return;
                }
                const igstRow = summaryCard.querySelector('[data-summary-row="igst"]');
                const cgstRow = summaryCard.querySelector('[data-summary-row="cgst"]');
                const sgstRow = summaryCard.querySelector('[data-summary-row="sgst"]');
                const igstValue = parseRowValue(igstRow);

                if (igstValue > 0) {
                    cgstRow?.classList.add('hidden');
                    sgstRow?.classList.add('hidden');
                    igstRow?.classList.remove('hidden');
                } else {
                    cgstRow?.classList.remove('hidden');
                    sgstRow?.classList.remove('hidden');
                    igstRow?.classList.add('hidden');
                }
            };

            const currencySymbol = @json($currencySymbol);
            const formatter = new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const formatCurrency = (value) => {
                const numeric = Number.isFinite(value) ? value : parseFloat(value);
                const readable = Number.isFinite(numeric) ? formatter.format(numeric) : '0.00';
                return `${currencySymbol}${readable}`;
            };
            const productCatalog = @json($quoteProductCatalog);

            const summaryFields = {
                subtotal: summaryCard?.querySelector('[data-summary-field="subtotal"]'),
                taxable: summaryCard?.querySelector('[data-summary-field="taxable"]'),
                cgst: summaryCard?.querySelector('[data-summary-field="cgst"]'),
                sgst: summaryCard?.querySelector('[data-summary-field="sgst"]'),
                igst: summaryCard?.querySelector('[data-summary-field="igst"]'),
                grand: summaryCard?.querySelector('[data-summary-field="grand"]'),
            };

            const updateSummaryField = (field, value) => {
                const element = summaryFields[field];
                if (!element) {
                    return;
                }
                element.textContent = formatCurrency(value);
            };

            const recalcTotals = () => {
                const rows = document.querySelectorAll('[data-quote-item]');
                let subtotal = 0;
                let totalGst = 0;
                rows.forEach(row => {
                    const qty = parseFloat(row.querySelector('[data-qty-input]')?.value) || 0;
                    const rate = parseFloat(row.querySelector('[data-rate-input]')?.value) || 0;
                    const gstPercent = parseFloat(row.querySelector('[data-gst-input]')?.value) || 0;
                    const discountPercent = parseFloat(row.querySelector('[data-discount-input]')?.value) || 0;
                    const taxableBase = qty * rate * (1 - Math.min(Math.max(discountPercent, 0), 100) / 100);
                    const gstAmount = taxableBase * (gstPercent / 100);
                    subtotal += taxableBase;
                    totalGst += gstAmount;
                    const rowAmountEl = row.querySelector('[data-row-amount]');
                    if (rowAmountEl) {
                        rowAmountEl.textContent = formatCurrency(taxableBase);
                    }
                    row.dataset.totalValue = taxableBase.toFixed(2);
                });

                const businessState = summaryCard?.dataset.businessState?.trim() ?? '';
                const clientStateText = stateEl?.textContent?.trim() ?? '';
                let cgst = 0;
                let sgst = 0;
                let igst = 0;

                if (businessState && clientStateText && businessState === clientStateText) {
                    cgst = totalGst / 2;
                    sgst = totalGst / 2;
                } else {
                    igst = totalGst;
                }

                updateSummaryField('subtotal', subtotal);
                updateSummaryField('taxable', subtotal);
                updateSummaryField('cgst', cgst);
                updateSummaryField('sgst', sgst);
                updateSummaryField('igst', igst);
                updateSummaryField('grand', subtotal + totalGst);
                if (summaryCard) {
                    const cgstRow = summaryCard.querySelector('[data-summary-row="cgst"]');
                    const sgstRow = summaryCard.querySelector('[data-summary-row="sgst"]');
                    const igstRow = summaryCard.querySelector('[data-summary-row="igst"]');
                    if (cgstRow) {
                        cgstRow.dataset.totalValue = cgst.toFixed(2);
                    }
                    if (sgstRow) {
                        sgstRow.dataset.totalValue = sgst.toFixed(2);
                    }
                    if (igstRow) {
                        igstRow.dataset.totalValue = igst.toFixed(2);
                    }
                }
                adjustTaxVisibility();
            };

            adjustTaxVisibility();

            if (window.Livewire?.hook) {
                window.Livewire.hook('message.processed', () => {
                    recalcTotals();
                });
            }

            const itemsTable = document.querySelector('[data-quote-items-table]');
            itemsTable?.addEventListener('input', (event) => {
                if (event.target.matches('[data-qty-input], [data-rate-input], [data-gst-input], [data-discount-input]')) {
                    recalcTotals();
                }
            });
            itemsTable?.addEventListener('change', (event) => {
                if (!event.target.matches('[data-quote-item-name]')) {
                    return;
                }

                const productName = event.target.value?.trim() ?? '';
                const entry = productCatalog[productName];

                if (!entry) {
                    return;
                }

                const row = event.target.closest('[data-quote-item]');
                const rateInput = row?.querySelector('[data-rate-input]');
                const gstInput = row?.querySelector('[data-gst-input]');

                if (rateInput) {
                    rateInput.value = entry.rate;
                    rateInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
                if (gstInput) {
                    gstInput.value = entry.gst_percent;
                    gstInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
            recalcTotals();
        });
    </script>
</form>
</div>
