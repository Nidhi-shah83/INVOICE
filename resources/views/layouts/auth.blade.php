<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>
            if (localStorage.theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>
        @php
            $brandName = setting('business_name', 'Invoice Pro');
            $pageTitle = trim($__env->yieldContent('page-title'));
            $faviconUrl = setting_media_url('favicon') ?: asset('favicon.ico');
        @endphp

        <title>{{ $pageTitle !== '' ? $pageTitle.' | '.$brandName : $brandName }}</title>
        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-100 font-inter text-gray-900 dark:bg-gray-900 dark:text-gray-100">
        <main class="relative flex min-h-screen items-center justify-center px-4 py-10">
            <div class="absolute inset-0 bg-gradient-to-b from-emerald-50 to-transparent dark:from-emerald-900/10 dark:to-transparent"></div>

            <section class="relative z-10 w-full max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-lg transition dark:border-gray-700 dark:bg-gray-800">
                <header class="mb-6 text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-gray-500 dark:text-gray-400">GST-ready invoicing</p>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $brandName }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">@yield('auth-subtitle', 'Manage quotes, invoices, and payments in one place.')</p>
                </header>

                @yield('content')
            </section>
        </main>
    </body>
</html>

