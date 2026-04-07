@extends('layouts.guest')

@section('page-title', 'Quote Already Approved')

@section('content')
    <div class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <p class="text-xs uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">Quote Approval</p>
        <h1 class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-100">This quote is already approved.</h1>
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
            Quote {{ $quote->quote_number }} has already been processed.
        </p>
        @if($order)
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Linked order: {{ $order->order_number }}
            </p>
        @endif
    </div>
@endsection
