@extends('layouts.guest')

@section('page-title', 'Quote Approved')

@section('content')
    <div class="rounded-3xl border border-emerald-200 bg-white p-8 text-center shadow-sm dark:border-emerald-900 dark:bg-slate-900">
        <p class="text-xs uppercase tracking-[0.3em] text-emerald-600 dark:text-emerald-400">Approval Complete</p>
        <h1 class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-100">Quote Approved Successfully</h1>
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
            Quote {{ $quote->quote_number }} has been approved.
        </p>
        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left dark:border-slate-700 dark:bg-slate-800/60">
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Order Created</p>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Order Number: {{ $order->order_number }}</p>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Status: {{ ucfirst($order->status) }}</p>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Total: {{ setting('currency_symbol', 'Rs') }}{{ number_format((float) $order->total_amount, 2) }}</p>
        </div>
    </div>
@endsection
