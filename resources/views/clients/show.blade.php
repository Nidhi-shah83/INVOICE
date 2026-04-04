@extends('layouts.app')

@section('page-title', $client->name)

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Client</p>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ $client->name }}</h1>
                    <p class="text-sm text-slate-500">
                        {{ $client->company_name ?? 'Individual customer' }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $client->email ?? 'Email not set' }} · {{ $client->phone ?? 'Phone not set' }}
                    </p>
                    @if ($client->alternate_phone)
                        <p class="text-xs text-slate-500">Alternate: {{ $client->alternate_phone }}</p>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">
                        {{ ucfirst($client->client_type) }}
                    </span>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $client->gst_type === 'intra' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700' }}">
                        {{ $client->gst_type === 'intra' ? 'Intra' : 'Inter' }}
                    </span>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Contact</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $client->email ?? 'Not provided' }}</p>
                    <p class="text-sm text-slate-600">{{ $client->phone ?? 'Phone not set' }}</p>
                    @if ($client->alternate_phone)
                        <p class="text-sm text-slate-600">Alternate: {{ $client->alternate_phone }}</p>
                    @endif
                </div>

                <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">GST compliance</p>
                    @if ($client->isBusiness())
                        <p class="text-sm font-semibold text-slate-900">{{ $client->gstin ?? 'GSTIN missing' }}</p>
                    @else
                        <p class="text-sm font-semibold text-slate-600">Individual clients do not require GSTIN</p>
                    @endif
                    <p class="text-sm text-slate-600">{{ $client->state }} · {{ $client->place_of_supply }}</p>
                </div>

                <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Location</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $client->city }} · {{ $client->pincode }}</p>
                    <p class="text-sm text-slate-600">{{ $client->country }}</p>
                    <p class="text-sm text-slate-600">{{ $client->address ?? 'No address provided' }}</p>
                </div>
            </div>

            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Notes</p>
                <h2 class="text-lg font-semibold text-slate-900">Internal context</h2>
                <p class="mt-4 text-sm text-slate-600">
                    {{ $client->notes ?? 'No notes yet. Use this space for internal reminders or conversations.' }}
                </p>
            </div>
        </div>

        <div x-data="{ tab: 'quotes' }" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex gap-2 border-b border-slate-100 pb-3 text-sm font-semibold text-slate-600">
                <button @click="tab = 'quotes'" :class="{ 'border-b-2 border-emerald-500 text-slate-900': tab === 'quotes' }" class="pb-2">Quotes</button>
                <button @click="tab = 'orders'" :class="{ 'border-b-2 border-emerald-500 text-slate-900': tab === 'orders' }" class="pb-2">Orders</button>
                <button @click="tab = 'invoices'" :class="{ 'border-b-2 border-emerald-500 text-slate-900': tab === 'invoices' }" class="pb-2">Invoices</button>
            </div>

            <template x-if="tab === 'quotes'">
                <div>
                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-900 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Quote #</th>
                                    <th class="px-4 py-3 text-left font-semibold">Issue</th>
                                    <th class="px-4 py-3 text-left font-semibold">Validity</th>
                                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($client->quotes as $quote)
                                    <tr>
                                        <td class="px-4 py-3">{{ $quote->quote_number }}</td>
                                        <td class="px-4 py-3">{{ $quote->issue_date->format('M j, Y') }}</td>
                                        <td class="px-4 py-3">{{ $quote->validity_date->format('M j, Y') }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize {{ $quote->status === 'accepted' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $quote->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">{{ number_format($quote->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">
                                            No quotes yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            <template x-if="tab === 'orders'">
                <div>
                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-900 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Order #</th>
                                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold">Total</th>
                                    <th class="px-4 py-3 text-right font-semibold">Billed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($client->orders as $order)
                                    <tr>
                                        <td class="px-4 py-3">{{ $order->order_number }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize {{ in_array($order->status, ['fulfilled', 'fully_billed']) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">{{ number_format($order->total_amount, 2) }}</td>
                                        <td class="px-4 py-3 text-right">{{ number_format($order->billed_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">
                                            No orders yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            <template x-if="tab === 'invoices'">
                <div>
                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-900 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Invoice #</th>
                                    <th class="px-4 py-3 text-left font-semibold">Issue</th>
                                    <th class="px-4 py-3 text-left font-semibold">Due</th>
                                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($client->invoices as $invoice)
                                    <tr>
                                        <td class="px-4 py-3">{{ $invoice->invoice_number }}</td>
                                        <td class="px-4 py-3">{{ $invoice->issue_date->format('M j, Y') }}</td>
                                        <td class="px-4 py-3">{{ $invoice->due_date->format('M j, Y') }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $invoice->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">{{ number_format($invoice->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">
                                            No invoices yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>
    </div>
@endsection
