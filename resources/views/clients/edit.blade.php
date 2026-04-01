@extends('layouts.app')

@section('page-title', 'Edit Client')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-6">
                @csrf
                @method('PUT')

                @include('clients.partials.form')

                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('clients.show', $client) }}" class="text-sm text-slate-500 hover:text-slate-900">Back</a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:bg-slate-800 transition">
                        Update client
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
