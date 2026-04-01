@extends('layouts.guest')

@section('content')
    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium text-slate-600" for="name">Full name</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                required
                autofocus
                class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm placeholder:text-slate-300 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
            @error('name')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600" for="email">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm placeholder:text-slate-300 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
            @error('email')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600" for="password">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm placeholder:text-slate-300 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
            @error('password')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600" for="password_confirmation">Confirm password</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm placeholder:text-slate-300 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
        </div>

        <button
            type="submit"
            class="w-full rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-lg hover:bg-slate-800 transition"
        >
            Create account
        </button>

        <p class="text-center text-xs text-slate-500">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-emerald-600 hover:text-emerald-500">Sign in</a>
        </p>
    </form>
@endsection
