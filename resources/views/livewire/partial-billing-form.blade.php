<div class="space-y-4 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
    <h3 class="text-lg font-semibold text-slate-900">Partial Billing</h3>

    @if ($errors->has('lines'))
        <p class="text-xs text-rose-600">{{ $errors->first('lines') }}</p>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Item</th>
                    <th class="px-4 py-3 text-right font-semibold">Remaining</th>
                    <th class="px-4 py-3 text-center font-semibold">Qty</th>
                    <th class="px-4 py-3 text-right font-semibold">Rate</th>
                    <th class="px-4 py-3 text-right font-semibold">Line Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach ($lines as $index => $line)
                    <tr>
                        <td class="px-4 py-3">{{ $line['name'] }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($line['qty_remaining'], 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            <input
                                type="number"
                                wire:model="lines.{{ $index }}.qty"
                                min="0"
                                step="0.01"
                                max="{{ $line['qty_remaining'] }}"
                                class="mx-auto w-24 rounded-2xl border border-slate-200 px-3 py-1 text-right text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                        </td>
                        <td class="px-4 py-3 text-right">₹{{ number_format($line['rate'], 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            ₹{{ number_format(($line['qty'] ?? 0) * $line['rate'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-2 text-sm text-slate-600">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Total</p>
            <p class="text-xl font-semibold text-slate-900">₹{{ number_format($totals['total'], 2) }}</p>
        </div>
        <div class="flex-1">
            <label class="text-xs uppercase tracking-[0.3em] text-slate-400" for="notes">Notes</label>
            <textarea
                id="notes"
                wire:model="notes"
                rows="2"
                class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            ></textarea>
        </div>
    </div>

    <div class="flex justify-end">
        <button
            wire:click.prevent="submit"
            class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-600 transition"
        >
            Create Invoice
        </button>
    </div>
</div>
