<form wire:submit.prevent="saveDraft" class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-slate-200 bg-white px-6 py-4 shadow-sm">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Quote Number</p>
            <p class="text-2xl font-semibold text-slate-900">{{ $quoteNumberPreview ?? 'Draft' }}</p>
        </div>
        <div class="text-right text-xs text-slate-500">
            <p>Business GSTIN: {{ config('invoice.gstin', 'XX0000XXXX') }}</p>
            <p>{{ config('invoice.business_name') }}</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div>
            <label class="block text-sm font-semibold text-slate-700" for="client">Client <span class="text-rose-500">*</span></label>
            <select
                id="client"
                wire:model="client_id"
                class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
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
                class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
            @error('issue_date') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700" for="validity_date">Validity</label>
            <input
                wire:model="validity_date"
                type="date"
                id="validity_date"
                class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
            @error('validity_date') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    @if($selectedClient)
        <div class="space-y-3 rounded-3xl border border-slate-200 bg-white p-4 text-sm text-slate-600 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Client</p>
                    <h3 class="text-lg font-semibold text-slate-900">{{ $selectedClient->name }}</h3>
                    <p class="text-xs text-slate-500">{{ $selectedClient->email ?? 'Email not set' }}</p>
                    @if($selectedClient->phone)
                        <p class="text-xs text-slate-500">{{ $selectedClient->phone }}</p>
                    @endif
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $selectedClient->gst_type === 'intra' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700' }}">
                    {{ $selectedClient->gst_type === 'intra' ? 'CGST+SGST' : 'IGST' }}
                </span>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                @if($selectedClient->gstin)
                    <div>
                        <span class="text-xs uppercase tracking-[0.3em] text-slate-400">GSTIN</span>
                        <p class="font-semibold text-slate-900">{{ $selectedClient->gstin }}</p>
                    </div>
                @endif
                <div>
                    <span class="text-xs uppercase tracking-[0.3em] text-slate-400">State</span>
                    <p class="font-semibold text-slate-900">{{ $selectedClient->state }}</p>
                </div>
            </div>
            @if($selectedClient->address)
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Address</p>
                    <p class="text-sm text-slate-500">{{ $selectedClient->address }}</p>
                </div>
            @endif
        </div>
    @endif

    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-900 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Item</th>
                        <th class="px-4 py-3 text-left font-semibold">Qty</th>
                        <th class="px-4 py-3 text-left font-semibold">Rate</th>
                        <th class="px-4 py-3 text-left font-semibold">GST%</th>
                        <th class="px-4 py-3 text-right font-semibold">Amount</th>
                        <th class="px-4 py-3 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($items as $index => $item)
                        <tr wire:key="item-{{ $index }}">
                            <td class="px-4 py-3">
                                <input
                                    type="text"
                                    wire:model="items.{{ $index }}.name"
                                    class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                >
                            </td>
                            <td class="px-4 py-3">
                                <input
                                    type="number"
                                    wire:model="items.{{ $index }}.qty"
                                    min="0"
                                    step="0.01"
                                    class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                >
                            </td>
                            <td class="px-4 py-3">
                                <input
                                    type="number"
                                    wire:model="items.{{ $index }}.rate"
                                    min="0"
                                    step="0.01"
                                    class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                >
                            </td>
                            <td class="px-4 py-3">
                                <input
                                    type="number"
                                    wire:model="items.{{ $index }}.gst_percent"
                                    min="0"
                                    step="0.01"
                                    class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                >
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ $currencySymbol }}{{ number_format(($item['qty'] ?? 0) * ($item['rate'] ?? 0), 2) }}
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
        <div class="grid gap-4 px-4 py-4 lg:grid-cols-2">
            <div class="space-y-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50/40 p-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Discount type</p>
                    <select
                        wire:model="discount_type"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                        <option value="flat">Flat amount</option>
                        <option value="percent">Percent</option>
                    </select>
                    @error('discount_type') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500" for="discount_value">Discount value</label>
                    <input
                        type="number"
                        id="discount_value"
                        wire:model="discount_value"
                        min="0"
                        step="0.01"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                    @error('discount_value') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Discount amount</span>
                    <span class="font-semibold text-slate-900">{{ $currencySymbol }}{{ number_format($totals['discount_amount'], 2) }}</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500" for="round_off">Round off</label>
                    <input
                        type="number"
                        id="round_off"
                        wire:model.lazy="round_off"
                        step="0.01"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                    @error('round_off') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400">Auto calculates to closest {{ $currencySymbol }}1 when left untouched.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500" for="currency">Currency</label>
                    <input
                        type="text"
                        id="currency"
                        wire:model="currency"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                    @error('currency') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <span>Subtotal</span>
                    <span>{{ $currencySymbol }}{{ number_format($totals['subtotal'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Taxable amount</span>
                    <span>{{ $currencySymbol }}{{ number_format($totals['taxable_amount'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>CGST</span>
                    <span>{{ $currencySymbol }}{{ number_format($totals['cgst'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>SGST</span>
                    <span>{{ $currencySymbol }}{{ number_format($totals['sgst'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>IGST</span>
                    <span>{{ $currencySymbol }}{{ number_format($totals['igst'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Total before round-off</span>
                    <span>{{ $currencySymbol }}{{ number_format($totals['total'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Round off</span>
                    <span>{{ $currencySymbol }}{{ number_format($totals['round_off'], 2) }}</span>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-900 px-4 py-3 text-right text-sm font-semibold text-white">
                    <p class="text-xs uppercase tracking-[0.3em] text-white/70">Grand total</p>
                    <p class="text-2xl">{{ $currencySymbol }}{{ number_format($totals['grand_total'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-between px-4 pb-4 text-xs text-slate-500">
            <p>Discount amount and round off refresh as you edit items or pricing.</p>
        </div>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Additional info</p>
            <span class="text-xs text-slate-500">Optional</span>
        </div>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-700" for="payment_terms">Payment terms</label>
                <textarea
                    id="payment_terms"
                    wire:model.defer="payment_terms"
                    rows="3"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                ></textarea>
                @error('payment_terms') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-700" for="terms_conditions">Terms & Conditions</label>
                <textarea
                    id="terms_conditions"
                    wire:model.defer="terms_conditions"
                    rows="3"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                ></textarea>
                @error('terms_conditions') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700" for="salesperson">Salesperson</label>
                <input
                    id="salesperson"
                    wire:model.defer="salesperson"
                    type="text"
                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
                @error('salesperson') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700" for="reference_no">Reference no.</label>
                <input
                    id="reference_no"
                    wire:model.defer="reference_no"
                    type="text"
                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
                @error('reference_no') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <label class="block text-sm font-semibold text-slate-700" for="notes">Notes</label>
        <textarea
            id="notes"
            wire:model.defer="notes"
            rows="3"
            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
        ></textarea>
    </section>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-slate-800 transition">
            Save as Draft
        </button>
        <button
            type="button"
            wire:click.prevent="sendNow"
            class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-600 transition"
        >
            Send Now
        </button>
    </div>
</form>
