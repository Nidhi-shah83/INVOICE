@extends('layouts.guest')

@section('page-title', 'Order Accepted')

@section('content')
    <div class="rounded-3xl border border-emerald-200 bg-white p-8 text-center shadow-sm dark:border-emerald-900 dark:bg-slate-900">
        <p class="text-xs uppercase tracking-[0.3em] text-emerald-600 dark:text-emerald-400">Order Accepted</p>
        <h1 class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-100">Order Accepted Successfully</h1>
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
            Order {{ $order->order_number }} has been accepted.
        </p>

        @if($invoice)
            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left dark:border-slate-700 dark:bg-slate-800/60">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Invoice Created</p>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Invoice ID: {{ $invoice->id }}</p>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Invoice Number: {{ $invoice->invoice_number }}</p>
            </div>
        @endif
    </div>
@endsection
