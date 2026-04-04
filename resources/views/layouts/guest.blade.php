<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Invoice App') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-inter text-slate-900">
        <main class="flex min-h-screen items-center justify-center px-4">
            <div class="w-full max-w-2xl space-y-6">
                <div class="text-center">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-500">GST-ready invoicing</p>
                    <h1 class="text-3xl font-semibold text-slate-900">{{ config('app.name', 'Invoice App') }}</h1>
                </div>
                @yield('content')
            </div>
        </main>
    </body>
</html>
