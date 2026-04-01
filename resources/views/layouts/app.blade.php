<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Invoice App') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-inter bg-slate-50 text-slate-900">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen flex">
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                class="fixed inset-0 bg-slate-900/60 z-30 lg:hidden"
                @click="sidebarOpen = false"
                aria-hidden="true"
            ></div>

            <aside
                class="fixed inset-y-0 left-0 w-64 bg-[#1F3864] text-white flex flex-col transition-transform duration-300 z-40"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full' + ' lg:translate-x-0'"
            >
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/10">
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-slate-200">{{ config('invoice.business_name') }}</p>
                        <p class="text-xs text-white/80">GSTIN: {{ config('invoice.gstin', 'XX0000XXXX') }}</p>
                    </div>
                    <button class="lg:hidden" @click="sidebarOpen = false">
                        <span class="sr-only">Close sidebar</span>
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1 text-sm" aria-label="Main">
                    @php
                        $navItems = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'pattern' => 'dashboard'],
                            ['label' => 'Quotes', 'route' => 'quotes.index', 'pattern' => 'quotes.*'],
                            ['label' => 'Orders', 'route' => 'orders.index', 'pattern' => 'orders.*'],
                            ['label' => 'Invoices', 'route' => 'invoices.index', 'pattern' => 'invoices.*'],
                            ['label' => 'Clients', 'route' => 'clients.index', 'pattern' => 'clients.*'],
                            ['label' => 'AI Assistant', 'route' => 'ai-assistant.index', 'pattern' => 'ai-assistant.*'],
                            ['label' => 'Reports', 'route' => 'reports.index', 'pattern' => 'reports.*'],
                            ['label' => 'Settings', 'route' => 'settings.index', 'pattern' => 'settings.*'],
                        ];
                    @endphp

                    @foreach ($navItems as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition hover:bg-white/10 {{ request()->routeIs($item['pattern']) ? 'bg-white/10' : '' }}"
                        >
                            <span class="text-base">•</span>
                            <span class="text-sm font-medium">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="px-4 py-6 bg-white/5">
                    <p class="text-xs uppercase tracking-[0.3em] text-white/70">State</p>
                    <p class="text-sm font-semibold">{{ config('invoice.state', 'Karnataka') }}</p>
                    <p class="text-xs text-white/60">Due days: {{ config('invoice.default_due_days', 15) }}</p>
                </div>
            </aside>

            <div class="flex-1 flex flex-col lg:pl-64">
                <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-slate-200 flex items-center justify-between px-6 py-4">
                    <div class="flex items-center gap-4">
                        <button
                            class="lg:hidden text-slate-600"
                            @click="sidebarOpen = true"
                        >
                            <span class="sr-only">Open sidebar</span>
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div>
                            <p class="text-xs uppercase tracking-widest text-slate-400">{{ config('invoice.invoice_prefix', 'INV') }} Portal</p>
                            <h1 class="text-2xl font-semibold text-slate-900">@yield('page-title', 'Dashboard')</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @hasSection('primary-action')
                            @yield('primary-action')
                        @endif
                        @auth
                            <div class="px-4 py-2 rounded-full bg-slate-100 text-slate-800 text-sm font-medium">
                                {{ Auth::user()->name }}
                            </div>
                        @endauth
                    </div>
                </header>

                <main class="flex-1 bg-slate-50 px-4 py-6 lg:px-10 lg:py-10">
                    <div class="max-w-6xl mx-auto">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
