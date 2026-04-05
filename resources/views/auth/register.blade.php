@extends('layouts.auth')

@section('page-title', 'Register')
@section('auth-subtitle', 'Create your Invoice Pro account and start billing smarter.')

@section('content')
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="name">Name</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                required
                autofocus
                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/30 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-green-400 dark:focus:ring-green-500/30"
            >
            @error('name')
                <p class="mt-1 text-xs text-red-600 dark:text-red-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="email">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
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

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="password_confirmation">Confirm Password</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/30 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-green-400 dark:focus:ring-green-500/30"
            >
        </div>

        <button
            type="submit"
            class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500/30"
        >
            Create Account
        </button>

        <p class="pt-1 text-center text-sm text-gray-600 dark:text-gray-300">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-green-600 transition hover:text-green-700 dark:text-green-400 dark:hover:text-green-300">Login</a>
        </p>
    </form>
@endsection
