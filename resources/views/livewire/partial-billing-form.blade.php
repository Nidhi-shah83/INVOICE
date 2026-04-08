<form wire:submit.prevent="submit" class="space-y-5 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Partial Billing</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Bill only the quantities that are still available on the order.</p>
        </div>

        @if ($order->remaining_amount <= 0)
            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                Fully Billed
            </span>
        @endif
    </div>

    @if ($errors->has('lines'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-200">
            {{ $errors->first('lines') }}
        </div>
    @endif

    <div class="overflow-x-auto hide-scrollbar">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Item</th>
                    <th class="px-4 py-3 text-right font-semibold">Ordered Qty</th>
                    <th class="px-4 py-3 text-right font-semibold">Billed Qty</th>
                    <th class="px-4 py-3 text-right font-semibold">Remaining Qty</th>
                    <th class="px-4 py-3 text-center font-semibold">Bill Now</th>
                    <th class="px-4 py-3 text-right font-semibold">Rate</th>
                    <th class="px-4 py-3 text-right font-semibold">Line Total</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                @foreach ($lines as $index => $line)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="space-y-1">
                                <p class="font-medium text-slate-900 dark:text-slate-100">{{ $line['name'] }}</p>
                                @if ((float) ($line['available_qty'] ?? 0) <= 0)
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        Fully Billed
                                    </span>
                                @endif
                            </div>
                        </td>

                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">
                            {{ number_format((float) ($line['ordered_qty'] ?? 0), 2) }}
                        </td>

                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">
                            {{ number_format((float) ($line['billed_qty'] ?? 0), 2) }}
                        </td>

                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">
                            {{ number_format(max(0, (float) ($line['available_qty'] ?? 0) - (float) ($line['qty'] ?? 0)), 2) }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <input
                                type="number"
                                wire:model.live.debounce.250ms="lines.{{ $index }}.qty"
                                min="0"
                                step="0.01"
                                max="{{ $line['available_qty'] }}"
                                @disabled((float) ($line['available_qty'] ?? 0) <= 0)
                                class="mx-auto w-28 rounded-2xl border border-slate-200 px-3 py-2 text-right text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:disabled:bg-slate-800"
                            >

                            @if ((float) ($line['qty'] ?? 0) > (float) ($line['available_qty'] ?? 0))
                                <p class="mt-1 text-[11px] font-medium text-rose-600 dark:text-rose-300">
                                    Max {{ number_format((float) ($line['available_qty'] ?? 0), 2) }}
                                </p>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">
                            ₹{{ number_format((float) ($line['rate'] ?? 0), 2) }}
                        </td>

                        <td class="px-4 py-3 text-right font-medium text-slate-900 dark:text-slate-100">
                            ₹{{ number_format(((float) ($line['qty'] ?? 0)) * ((float) ($line['rate'] ?? 0)), 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Total</p>
            <p class="text-xl font-semibold text-slate-900 dark:text-slate-100">₹{{ number_format((float) ($totals['total'] ?? 0), 2) }}</p>
        </div>

        <div class="flex-1">
            <label class="text-xs uppercase tracking-[0.3em] text-slate-400" for="notes">Notes</label>
            <textarea
                id="notes"
                wire:model="notes"
                rows="2"
                class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
            ></textarea>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <button
            type="button"
            wire:click="billRemaining"
            wire:loading.attr="disabled"
            wire:target="billRemaining"
            @disabled($order->remaining_amount <= 0)
            class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-5 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        >
            Fill Remaining
        </button>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="submit"
            @disabled($order->remaining_amount <= 0)
            class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow-lg transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-60"
        >
            <svg wire:loading wire:target="submit" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span wire:loading.remove wire:target="submit">Create Invoice</span>
            <span wire:loading wire:target="submit">Creating...</span>
        </button>
    </div>

</form>
