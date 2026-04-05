<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @php
            $brandName = setting('business_name', config('app.name', 'Invoice App'));
            $pageTitle = trim($__env->yieldContent('page-title'));
            $faviconPath = setting('favicon');
            $faviconPath = is_string($faviconPath) ? ltrim(preg_replace('#^/?storage/#', '', $faviconPath), '/') : null;
            $faviconUrl = asset('favicon.ico');

            if (! empty($faviconPath) && \Illuminate\Support\Facades\Storage::disk('public')->exists($faviconPath)) {
                $faviconMime = \Illuminate\Support\Facades\Storage::disk('public')->mimeType($faviconPath) ?: 'image/png';
                $faviconUrl = 'data:'.$faviconMime.';base64,'.base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($faviconPath));
            }
        @endphp

        <title>{{ $pageTitle !== '' ? $pageTitle.' | '.$brandName : $brandName }}</title>
        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">

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
                    <h1 class="text-3xl font-semibold text-slate-900">{{ $brandName }}</h1>
                </div>
                @yield('content')
            </div>
        </main>
    </body>
</html>
