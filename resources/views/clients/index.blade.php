@extends('layouts.app')

@section('page-title', 'Clients')

@section('primary-action')
    <a
        href="{{ route('clients.create') }}"
        class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-600 transition"
    >
        + Add client
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @livewire('client-index')
        </div>
    </div>
@endsection
