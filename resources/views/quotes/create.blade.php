@extends('layouts.app')

@section('page-title', 'New Quote')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @livewire('quote-form')
        </div>
    </div>
@endsection
