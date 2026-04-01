@extends('layouts.guest')

@section('content')
    @if (session('status'))
        <div class="rounded-2xl border border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium text-slate-600" for="email">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
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

        <div class="flex items-center justify-between text-sm text-slate-500">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                Remember me
            </label>
            <a href="#" class="font-semibold text-emerald-600 hover:text-emerald-500">Forgot password?</a>
        </div>

        <button
            type="submit"
            class="w-full rounded-2xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-600 transition"
        >
            Sign in
        </button>

        <p class="text-center text-xs text-slate-500">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-semibold text-emerald-600 hover:text-emerald-500">Register</a>
        </p>
    </form>
@endsection
