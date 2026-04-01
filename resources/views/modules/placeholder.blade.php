@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.app')

@section('page-title', $module)

@section('primary-action')
    @if (!empty($primaryAction))
        <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-emerald-600 transition"
        >
            {{ $primaryAction }}
        </button>
    @endif
@endsection

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ $module }} · {{ $action }}</h2>
            <p class="mt-3 text-sm text-slate-500">
                Everything you see here is a stub that keeps controllers lean. The {{ $module }} service
                is responsible for business rules and data preparation.
            </p>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-dashed border-slate-200 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Next steps</p>
                <ul class="mt-3 list-disc space-y-1 pl-4 text-sm text-slate-600">
                    <li>Wire up the {{ $module }} service to real repositories.</li>
                    <li>Replace this placeholder view once you have UI/UX for {{ Str::lower($module) }}.</li>
                </ul>
            </div>
            <div class="rounded-2xl border border-dashed border-slate-200 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Service</p>
                <p class="text-base font-semibold text-slate-900">{{ class_basename($serviceClass ?? 'ModuleService') }}</p>
            </div>
        </div>
    </div>
@endsection
