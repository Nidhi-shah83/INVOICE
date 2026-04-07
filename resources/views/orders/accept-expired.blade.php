@extends('layouts.guest')

@section('page-title', 'Link Expired')

@section('content')
    <div class="rounded-3xl border border-amber-200 bg-white p-8 text-center shadow-sm dark:border-amber-900 dark:bg-slate-900">
        <p class="text-xs uppercase tracking-[0.3em] text-amber-600 dark:text-amber-400">Link Expired</p>
        <h1 class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-100">Acceptance link has expired.</h1>
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
            The acceptance link for order {{ $order->order_number }} is valid for 24 hours only.
        </p>
    </div>
@endsection
