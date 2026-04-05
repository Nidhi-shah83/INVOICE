@extends('layouts.auth')

@section('page-title', 'Login')
@section('auth-subtitle', 'Sign in to continue to your Invoice Pro dashboard.')

@section('content')
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="email">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/30 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-green-400 dark:focus:ring-green-500/30"
            >
            @error('email')
                <p class="mt-1 text-xs text-red-600 dark:text-red-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="password">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/30 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-green-400 dark:focus:ring-green-500/30"
            >
            @error('password')
                <p class="mt-1 text-xs text-red-600 dark:text-red-300">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500 dark:border-gray-600 dark:bg-gray-900">
                Remember me
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="font-semibold text-green-600 transition hover:text-green-700 dark:text-green-400 dark:hover:text-green-300">
                    Forgot password?
                </a>
            @endif
        </div>

        <button
            type="submit"
            class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500/30"
        >
            Sign in
        </button>

        <p class="pt-1 text-center text-sm text-gray-600 dark:text-gray-300">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-semibold text-green-600 transition hover:text-green-700 dark:text-green-400 dark:hover:text-green-300">Register</a>
        </p>
    </form>
@endsection
