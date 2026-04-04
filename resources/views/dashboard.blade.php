@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('primary-action')
    <a
        href="{{ route('invoices.create') }}"
        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-700 transition-colors"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Invoice
    </a>
@endsection

@section('content')
<div class="space-y-6">

    {{-- STAT CARDS: 4 Key Metrics --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Revenue Card -->
        <div class="group relative overflow-hidden rounded-lg bg-white shadow-sm hover:shadow-md transition-shadow">
            <div class="absolute left-0 top-0 h-full w-1 bg-gradient-to-b from-emerald-400 to-emerald-600"></div>
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Total Revenue</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">₹{{ number_format($total_revenue, 2) }}</p>
                        <p class="mt-1 text-xs text-slate-400">This month</p>
                    </div>
                    <div class="rounded-lg bg-emerald-100 p-3 text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <a href="#" class="mt-3 inline-flex text-xs font-medium text-emerald-600 hover:text-emerald-700">
                    View Details →
                </a>
            </div>
        </div>

        <!-- Unpaid Amount Card -->
        <div class="group relative overflow-hidden rounded-lg bg-white shadow-sm hover:shadow-md transition-shadow">
            <div class="absolute left-0 top-0 h-full w-1 bg-gradient-to-b from-yellow-400 to-yellow-600"></div>
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Unpaid Amount</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">₹{{ number_format($unpaid_amount, 2) }}</p>
                        <p class="mt-1 text-xs text-slate-400">Awaiting payment</p>
                    </div>
                    <div class="rounded-lg bg-yellow-100 p-3 text-yellow-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <a href="{{ route('invoices.index') }}?filter=unpaid" class="mt-3 inline-flex text-xs font-medium text-yellow-600 hover:text-yellow-700">
                    View Unpaid →
                </a>
            </div>
        </div>

        <!-- Overdue Amount Card -->
        <div class="group relative overflow-hidden rounded-lg bg-white shadow-sm hover:shadow-md transition-shadow">
            <div class="absolute left-0 top-0 h-full w-1 bg-gradient-to-b from-red-400 to-red-600"></div>
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Overdue Amount</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">₹{{ number_format($overdue_amount, 2) }}</p>
                        <p class="mt-1 text-xs text-slate-400">Past due date</p>
                    </div>
                    <div class="rounded-lg bg-red-100 p-3 text-red-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <a href="{{ route('invoices.index') }}?filter=overdue" class="mt-3 inline-flex text-xs font-medium text-red-600 hover:text-red-700">
                    View Overdue →
                </a>
            </div>
        </div>

        <!-- Active Orders Card -->
        <div class="group relative overflow-hidden rounded-lg bg-white shadow-sm hover:shadow-md transition-shadow">
            <div class="absolute left-0 top-0 h-full w-1 bg-gradient-to-b from-blue-400 to-blue-600"></div>
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Active Orders</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $active_orders }}</p>
                        <p class="mt-1 text-xs text-slate-400">In progress</p>
                    </div>
                    <div class="rounded-lg bg-blue-100 p-3 text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>
                <a href="{{ route('orders.index') }}" class="mt-3 inline-flex text-xs font-medium text-blue-600 hover:text-blue-700">
                    View Orders →
                </a>
            </div>
        </div>
    </div>

    {{-- OVERDUE ALERT BANNER --}}
    @if(count($overdue_invoices) > 0)
    <div class="rounded-lg border-l-4 border-red-500 bg-red-50 p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div class="flex items-start gap-3">
                <div class="mt-0.5">
                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-red-900">⚠ You have {{ count($overdue_invoices) }} overdue {{ count($overdue_invoices) > 1 ? 'invoices' : 'invoice' }}</h3>
                    <p class="mt-1 text-sm text-red-800">Immediate action required to recover outstanding payments</p>
                </div>
            </div>
            <button type="button" class="text-red-400 hover:text-red-600">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        {{-- Overdue Invoices List in Banner --}}
        <div class="mt-4 space-y-2">
            @foreach($overdue_invoices as $invoice)
            <div class="flex items-center justify-between rounded bg-white p-3 text-sm">
                <div class="flex items-center gap-3">
                    <div class="font-mono font-semibold text-slate-900">{{ $invoice['invoice_number'] }}</div>
                    <div>
                        <p class="text-slate-700">{{ $invoice['client_name'] }}</p>
                        <p class="text-xs text-slate-500">{{ $invoice['days_overdue'] }} days overdue</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="font-semibold text-red-600">₹{{ number_format($invoice['amount_due'], 2) }}</p>
                        <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($invoice['due_date'])->format('d M Y') }}</p>
                    </div>
                    <button type="button" class="ml-2 inline-flex items-center gap-1 rounded bg-red-100 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-200 transition-colors">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        Send Reminder
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- RECENT INVOICES TABLE --}}
        <div class="col-span-1 xl:col-span-2 rounded-lg bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Recent Invoices</h2>
                <a href="{{ route('invoices.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
                    View All →
                </a>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Invoice</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Client</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Amount</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Due Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($recent_invoices as $invoice)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3">
                                <a href="{{ route('invoices.show', $invoice['id']) }}" class="font-mono font-semibold text-slate-900 hover:text-emerald-600">
                                    {{ $invoice['invoice_number'] }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $invoice['client_name'] }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">₹{{ number_format($invoice['amount'], 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($invoice['payment_status'] === 'paid')
                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Paid</span>
                                @elseif($invoice['payment_status'] === 'partial')
                                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">Partial</span>
                                @else
                                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">Unpaid</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ \Carbon\Carbon::parse($invoice['due_date'])->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                No invoices found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TOP CLIENTS TABLE --}}
        <div class="col-span-1 rounded-lg bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Top Clients</h2>
            </div>

            <div class="mt-6 space-y-4">
                @forelse($top_clients as $client)
                <div class="rounded-lg border border-slate-200 p-4 hover:border-slate-300 transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <h3 class="font-semibold text-slate-900">{{ $client['client_name'] }}</h3>
                            <p class="mt-1 text-xs text-slate-500">{{ $client['state'] ?? 'N/A' }}</p>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                    {{ $client['invoice_count'] }} invoice{{ $client['invoice_count'] !== 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-slate-900">₹{{ number_format($client['total_billed'], 2) }}</p>
                            <p class="text-xs text-slate-500">Total Billed</p>
                        </div>
                    </div>
                </div>
                @empty
                <p class="py-8 text-center text-slate-500">No client data yet</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- AI FOLLOW-UP ACTIVITY TABLE (CORE FEATURE) --}}
    @if(count($followup_activity) > 0)
    <div class="rounded-lg bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900">
                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7 2a1 1 0 11.707 1.707A1 1 0 017 2zM4 4a1 1 0 11.707 1.707A1 1 0 014 4zm10 0a1 1 0 11.707 1.707A1 1 0 0114 4zm-8.707 8.707a1 1 0 11-1.414-1.414 1 1 0 011.414 1.414zM7 10a1 1 0 11-2 0 1 1 0 012 0zm8 0a1 1 0 11-2 0 1 1 0 012 0zm-8-6a1 1 0 11.707-1.707A1 1 0 007 4zm0 12a1 1 0 11.707 1.707A1 1 0 007 16zm8-8a1 1 0 11.707 1.707A1 1 0 0015 8z" clip-rule="evenodd" />
                </svg>
                AI Follow-up Activity
            </h2>
            <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-700">{{ count($followup_activity) }} Calls</span>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Invoice</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Client</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Amount Due</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-600">Days Overdue</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Last Contact</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Promised Date</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-600">Confidence</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($followup_activity as $log)
                    <tr class="hover:bg-slate-50 transition-colors cursor-pointer" onclick="openCallModal({{ json_encode($log) }})">
                        <td class="px-4 py-3">
                            <span class="font-mono font-semibold text-slate-900">{{ $log['invoice_number'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $log['client_name'] }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-900">₹{{ number_format($log['amount_due'], 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($log['days_overdue'] > 0)
                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                    {{ $log['days_overdue'] }} days
                                </span>
                            @else
                                <span class="text-slate-500">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            @if($log['last_contact'])
                                {{ \Carbon\Carbon::parse($log['last_contact'])->diffForHumans() }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($log['promised_payment_date'])
                                <span class="text-slate-700 font-medium">{{ \Carbon\Carbon::parse($log['promised_payment_date'])->format('d M Y') }}</span>
                            @else
                                <span class="text-slate-400">Not set</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($log['confidence'] === 'high')
                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">High</span>
                            @elseif($log['confidence'] === 'medium')
                                <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">Medium</span>
                            @else
                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Low</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- CALL LOG MODAL --}}
    <div id="callModal" class="pointer-events-none fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 opacity-0 transition-opacity">
        <div class="pointer-events-auto rounded-lg bg-white shadow-xl" style="max-height: 90vh; overflow-y-auto; width: 100%; max-width: 600px;">
            <div class="sticky top-0 flex items-center justify-between border-b border-slate-200 bg-white p-6">
                <h3 class="text-lg font-semibold text-slate-900">Call Details</h3>
                <button type="button" onclick="closeCallModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500">Invoice #</p>
                        <p id="modalInvoiceNumber" class="mt-1 font-mono font-semibold text-slate-900">—</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500">Client</p>
                        <p id="modalClientName" class="mt-1 font-semibold text-slate-900">—</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500">Amount Due</p>
                        <p id="modalAmountDue" class="mt-1 font-semibold text-slate-900">—</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500">Promised Payment</p>
                        <p id="modalPromisedDate" class="mt-1 text-slate-700">—</p>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Last Contact</p>
                    <p id="modalLastContact" class="mt-1 text-slate-700">—</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Conversation Notes</p>
                    <div id="modalNotes" class="mt-2 rounded-lg bg-slate-50 p-4 font-mono text-sm text-slate-700">—</div>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">AI Conversation</p>
                    <div id="modalConversation" class="mt-2 rounded-lg bg-slate-50 p-4 font-mono text-xs text-slate-700 max-h-48 overflow-y-auto">—</div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<script>
function openCallModal(logData) {
    document.getElementById('modalInvoiceNumber').textContent = logData.invoice_number;
    document.getElementById('modalClientName').textContent = logData.client_name;
    document.getElementById('modalAmountDue').textContent = '₹' + parseFloat(logData.amount_due).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    document.getElementById('modalPromisedDate').textContent = logData.promised_payment_date ? new Date(logData.promised_payment_date).toLocaleDateString('en-IN') : 'Not set';
    
    if (logData.last_contact) {
        const date = new Date(logData.last_contact);
        document.getElementById('modalLastContact').textContent = date.toLocaleString('en-IN');
    }
    
    document.getElementById('modalNotes').textContent = logData.notes || 'No notes';
    document.getElementById('modalConversation').textContent = logData.conversation || 'No conversation recorded';
    
    const modal = document.getElementById('callModal');
    modal.style.opacity = '1';
    modal.style.pointerEvents = 'auto';
}

function closeCallModal() {
    const modal = document.getElementById('callModal');
    modal.style.opacity = '0';
    modal.style.pointerEvents = 'none';
}

document.getElementById('callModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeCallModal();
});
</script>
@endsection

