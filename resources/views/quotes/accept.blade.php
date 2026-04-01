@extends('layouts.app')

@section('page-title', $already ? 'Quote already accepted' : 'Quote accepted')

@section('content')
    <div class="flex min-h-screen items-center justify-center py-16">
        <div class="w-full max-w-2xl space-y-6 rounded-3xl border border-slate-200 bg-white p-10 shadow-lg text-center">
            <h1 class="text-2xl font-semibold text-slate-900">
                {{ $already ? 'This quote has already been accepted.' : 'Your quote has been accepted!' }}
            </h1>
            <p class="text-sm text-slate-500">
                {{ $already ? 'We will contact you with next steps soon.' : 'Thank you! We will contact you shortly to finalize the details.' }}
            </p>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-slate-900 px-6 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">
                Back to dashboard
            </a>
        </div>
    </div>
@endsection
