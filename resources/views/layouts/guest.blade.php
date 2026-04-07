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
            $brandName = setting('business_name', config('app.name', 'Invoice App'));
            $pageTitle = trim($__env->yieldContent('page-title'));
            $faviconUrl = setting_media_url('favicon') ?: asset('favicon.ico');
        @endphp

        <title>{{ $pageTitle !== '' ? $pageTitle.' | '.$brandName : $brandName }}</title>
        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-inter text-slate-900 dark:bg-slate-950 dark:text-slate-100">
        <main class="flex min-h-screen items-center justify-center px-4">
            <div class="w-full max-w-2xl space-y-6">
                <div class="text-center">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">GST-ready invoicing</p>
                    <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ $brandName }}</h1>
                </div>
                @yield('content')
            </div>
        </main>
    </body>
</html>

