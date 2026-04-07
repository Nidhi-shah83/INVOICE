@extends('layouts.guest')

@section('page-title', 'Order Rejected')

@section('content')
    <div class="rounded-3xl border border-rose-200 bg-white p-8 text-center shadow-sm dark:border-rose-900 dark:bg-slate-900">
        <p class="text-xs uppercase tracking-[0.3em] text-rose-600 dark:text-rose-400">Order Rejected</p>
        <h1 class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-100">
            {{ $already ? 'This order was already rejected.' : 'Order Rejected Successfully' }}
        </h1>
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
            Order {{ $order->order_number }} has been {{ $already ? 'marked as rejected already.' : 'marked as rejected.' }}
        </p>
    </div>
@endsection
