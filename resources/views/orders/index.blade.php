@extends('layouts.app')

@section('page-title', 'Orders')

@section('content')
    <div class="space-y-6">
        @if(session('status'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex flex-wrap gap-2 text-sm font-semibold text-slate-500">
                @foreach($statusTabs as $tab)
                    <a
                        href="{{ route('orders.index', ['status' => $tab]) }}"
                        class="px-3 py-2 transition {{ $activeStatus === $tab ? 'rounded-2xl bg-slate-900 text-white' : 'rounded-2xl border border-slate-200 hover:border-slate-900' }}"
                    >
                        {{ ucfirst($tab) }}
                    </a>
                @endforeach
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-100">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Order #</th>
                            <th class="px-4 py-3 text-left font-semibold">Client</th>
                            <th class="px-4 py-3 text-right font-semibold">Total</th>
                            <th class="px-4 py-3 text-right font-semibold">Billed</th>
                            <th class="px-4 py-3 text-right font-semibold">Remaining</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($orders as $order)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $order->order_number }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $order->client->name }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900">₹{{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-4 py-3 text-right text-slate-500">₹{{ number_format($order->billed_amount, 2) }}</td>
                                <td class="px-4 py-3 text-right text-slate-500">₹{{ number_format($order->remaining_amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $order->status === 'fully_billed' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('orders.show', $order) }}" class="text-slate-600 hover:text-slate-900 font-semibold">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">No orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $orders->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
