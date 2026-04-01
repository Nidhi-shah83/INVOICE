@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('primary-action')
    <a
        href="{{ route('invoices.create') }}"
        class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-600 transition"
    >
        New Invoice
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @foreach ($summary as $item)
                <div class="rounded-2xl border border-slate-200 bg-white/80 p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">{{ $item['label'] }}</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $item['value'] }}</p>
                    @if (!empty($item['detail']))
                        <p class="mt-1 text-sm text-slate-500">{{ $item['detail'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Business setup</p>
                    <h2 class="text-2xl font-semibold text-slate-900">{{ $business['name'] ?? config('invoice.business_name') }}</h2>
                </div>
                <span class="text-sm text-slate-500">Prefill for invoices, quotes, and orders</span>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="space-y-1 rounded-2xl border border-dashed border-slate-200 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Invoice prefix</p>
                    <p class="text-lg font-semibold text-slate-900">{{ $business['prefixes']['invoice'] }}</p>
                </div>
                <div class="space-y-1 rounded-2xl border border-dashed border-slate-200 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Quote prefix</p>
                    <p class="text-lg font-semibold text-slate-900">{{ $business['prefixes']['quote'] }}</p>
                </div>
                <div class="space-y-1 rounded-2xl border border-dashed border-slate-200 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Order prefix</p>
                    <p class="text-lg font-semibold text-slate-900">{{ $business['prefixes']['order'] }}</p>
                </div>
                <div class="space-y-1 rounded-2xl border border-dashed border-slate-200 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Default due days</p>
                    <p class="text-lg font-semibold text-slate-900">{{ $business['defaults']['due_days'] }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
