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
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-inter bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
        @php
            $businessLogoUrl = setting_media_url('logo', 'business_logo');
            $businessName = setting('business_name', 'Invoice Portal');
            $businessInitial = strtoupper(substr($businessName, 0, 2));
            $businessGstin = setting('gstin', '');
            $stateName = setting('state');
            $dueDays = setting('default_due_days', 15);
        @endphp
        <div x-data="layoutShell()" x-init="initTheme()" class="min-h-screen flex">
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
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-white/95 shadow-sm flex items-center justify-center overflow-hidden p-2 transition-transform duration-200 hover:scale-105">
                            @if ($businessLogoUrl)
                                <img src="{{ $businessLogoUrl }}" alt="{{ $businessName }} logo" class="max-h-full max-w-full object-contain">
                            @else
                                <span class="flex h-full w-full items-center justify-center rounded-lg bg-gradient-to-br from-blue-500/90 via-indigo-500/90 to-purple-600/90 text-lg font-bold tracking-wider text-white leading-none">
                                    {{ $businessInitial }}
                                </span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold tracking-wide text-white drop-shadow-sm">{{ $businessName }}</p>
                            <p class="text-xs text-white/60">{{ $businessGstin ? 'GSTIN: '.$businessGstin : 'GSTIN not set' }}</p>
                        </div>
                    </div>
                    <button class="lg:hidden" @click="sidebarOpen = false">
                        <span class="sr-only">Close sidebar</span>
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1 text-sm sidebar-scrollbar" aria-label="Main">
                    @php
                        $navItems = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'pattern' => 'dashboard', 'icon' => 'home'],
                            ['label' => 'Quotes', 'route' => 'quotes.index', 'pattern' => 'quotes.*', 'icon' => 'quote'],
                            ['label' => 'Orders', 'route' => 'orders.index', 'pattern' => 'orders.*', 'icon' => 'order'],
                            ['label' => 'Invoices', 'route' => 'invoices.index', 'pattern' => 'invoices.*', 'icon' => 'invoice'],
                            ['label' => 'Clients', 'route' => 'clients.index', 'pattern' => 'clients.*', 'icon' => 'clients'],
                            ['label' => 'Items', 'route' => 'products.index', 'pattern' => 'products.*', 'icon' => 'items'],
                            ['label' => 'AI Assistant', 'route' => 'ai-assistant.index', 'pattern' => 'ai-assistant.*', 'icon' => 'ai'],
                            ['label' => 'Reports', 'route' => 'reports.index', 'pattern' => 'reports.*', 'icon' => 'reports'],
                            ['label' => 'Settings', 'route' => 'settings.index', 'pattern' => 'settings.*', 'icon' => 'settings'],
                        ];
                    @endphp

                    @foreach ($navItems as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition hover:bg-white/10 {{ request()->routeIs($item['pattern']) ? 'bg-white/10' : '' }}"
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white/90">
                                @switch($item['icon'])
                                    @case('home')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 11 9-8 9 8" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 10v10h14V10" />
                                        </svg>
                                        @break
                                    @case('quote')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10M7 12h10M7 17h6" />
                                        </svg>
                                        @break
                                    @case('order')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10l3 4-8 8-8-8 3-4z" />
                                        </svg>
                                        @break
                                    @case('invoice')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 4h10v16l-2-1.5-2 1.5-2-1.5-2 1.5-2-1.5-2 1.5V4z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 8h6M9 12h6M9 16h4" />
                                        </svg>
                                        @break
                                    @case('clients')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20a5 5 0 00-10 0" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 100-8 4 4 0 000 8z" />
                                        </svg>
                                        @break
                                    @case('items')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4-9 4-9-4z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10l9 4 9-4V7" />
                                        </svg>
                                        @break
                                    @case('ai')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l1.6 4.8L18 9.4l-4.4 1.6L12 16l-1.6-4.8L6 9.4l4.4-1.6L12 3z" />
                                        </svg>
                                        @break
                                    @case('reports')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16v-4M12 16V8m4 8v-6" />
                                        </svg>
                                        @break
                                    @case('settings')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.7 1.7 0 00.34 1.87l.06.06a2 2 0 01-1.42 3.42h-.08a1.7 1.7 0 00-1.6 1.1 1.7 1.7 0 00-.06.46 2 2 0 01-4 0 1.7 1.7 0 00-1.1-1.6 1.7 1.7 0 00-.46-.06 1.7 1.7 0 00-1.6 1.1 1.7 1.7 0 00-.06.46 2 2 0 01-4 0 1.7 1.7 0 00-1.1-1.6 1.7 1.7 0 00-.46-.06 2 2 0 01-1.42-3.42l.06-.06A1.7 1.7 0 005 15a1.7 1.7 0 00-1.1-1.6 1.7 1.7 0 00-.46-.06 2 2 0 010-4 1.7 1.7 0 001.6-1.1 1.7 1.7 0 00.06-.46 2 2 0 014 0 1.7 1.7 0 001.1 1.6 1.7 1.7 0 00.46.06 1.7 1.7 0 001.6-1.1 1.7 1.7 0 00.06-.46 2 2 0 014 0 1.7 1.7 0 001.1 1.6 1.7 1.7 0 00.46.06 2 2 0 011.42 3.42l-.06.06A1.7 1.7 0 0019.4 15z" />
                                        </svg>
                                        @break
                                @endswitch
                            </span>
                            <span class="text-sm font-medium">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="px-4 py-6 bg-white/5">
                    @if($stateName)
                        <p class="text-xs uppercase tracking-[0.3em] text-white/70">State</p>
                        <p class="text-sm font-semibold">{{ $stateName }}</p>
                    @endif
                    <p class="text-xs text-white/60">Due days: {{ $dueDays }}</p>
                </div>
            </aside>

            <div class="flex-1 flex flex-col lg:pl-64">
                <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-slate-200 flex items-center justify-between px-6 py-4 dark:border-slate-800 dark:bg-slate-900/90">
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
                        @if(!request()->routeIs('dashboard'))
                            <button
                                onclick="window.history.back()"
                                class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white"
                            >
                                <span class="sr-only">Go back</span>
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                        @endif
                        <div>
                            <p class="text-xs uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ setting('invoice_prefix', 'INV') }} Portal</p>
                            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">@yield('page-title', 'Dashboard')</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @hasSection('primary-action')
                            @yield('primary-action')
                        @endif

                        @auth
                            @php
                                $userName = auth()->user()->name;
                                $nameParts = preg_split('/\s+/', trim($userName)) ?: [];
                                $userInitials = collect($nameParts)
                                    ->filter()
                                    ->take(2)
                                    ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                    ->implode('');
                                $userInitials = $userInitials !== '' ? $userInitials : strtoupper(substr($userName, 0, 1));
                            @endphp

                            <button
                                type="button"
                                @click="toggleTheme()"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:text-white"
                            >
                                <span class="sr-only">Toggle dark mode</span>
                                <svg x-show="theme === 'light'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                                </svg>
                                <svg x-show="theme === 'dark'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m8.66-9H21m-18 0h.34M18.36 5.64l-.7.7M6.34 17.66l-.7.7m12.72 0l-.7-.7M6.34 6.34l-.7-.7M12 7a5 5 0 100 10 5 5 0 000-10z" />
                                </svg>
                            </button>

                            <div
                                x-data="{
                                    notificationsOpen: false,
                                    notifications: [],
                                    unreadCount: 0,
                                    loading: false,
                                    error: false,
                                    init() {
                                        this.fetchNotifications();
                                        setInterval(() => this.fetchUnreadCount(), 45000);
                                    },
                                    fetchNotifications() {
                                        this.loading = true;
                                        fetch('{{ route('notifications.index') }}', {
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest',
                                            },
                                        })
                                            .then((response) => response.json())
                                            .then((data) => {
                                                this.notifications = data.notifications;
                                                this.unreadCount = data.unread_count;
                                            })
                                            .catch(() => {
                                                this.error = true;
                                            })
                                            .finally(() => {
                                                this.loading = false;
                                            });
                                    },
                                    fetchUnreadCount() {
                                        fetch('{{ route('notifications.index') }}', {
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest',
                                            },
                                        })
                                            .then((response) => response.json())
                                            .then((data) => {
                                                this.unreadCount = data.unread_count;
                                            });
                                    },
                                    markAllRead() {
                                        fetch('{{ route('notifications.markAllRead') }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                            },
                                        }).then(() => {
                                            this.unreadCount = 0;
                                            this.fetchNotifications();
                                        });
                                    },
                                }"
                                x-init="init()"
                                @click.outside="notificationsOpen = false"
                                class="relative"
                            >
                                <button
                                    type="button"
                                    @click="notificationsOpen = ! notificationsOpen"
                                    class="relative inline-flex items-center justify-center h-11 w-11 rounded-full border border-slate-200 bg-white text-slate-600 hover:text-slate-900 transition dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:text-white"
                                >
                                    <span class="sr-only">Open notifications</span>
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0h6z" />
                                    </svg>
                                    <span
                                        x-show="unreadCount > 0"
                                        x-text="unreadCount"
                                        class="absolute -top-1 -end-1 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-600 px-1.5 text-xs font-semibold text-white"
                                    ></span>
                                </button>

                                <div
                                    x-show="notificationsOpen"
                                    x-cloak
                                    x-transition.duration.200ms
                                    class="absolute right-0 mt-3 w-[360px] origin-top-right divide-y divide-slate-200 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl dark:divide-slate-700 dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <div class="px-4 py-4 flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Notifications</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Latest alerts for collection actions</p>
                                        </div>
                                        <button
                                            type="button"
                                            class="text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                                            @click.prevent="markAllRead()"
                                        >
                                            Mark all read
                                        </button>
                                    </div>

                                    <div class="max-h-96 overflow-y-auto">
                                        <template x-if="loading">
                                            <div class="px-4 py-6 text-center text-sm text-slate-500">Loading notifications…</div>
                                        </template>

                                        <template x-if="!loading && notifications.length === 0">
                                            <div class="px-4 py-6 text-center text-sm text-slate-500">No new notifications</div>
                                        </template>

                                        <template x-for="notification in notifications" :key="notification.id">
                                            <div class="px-4 py-4 border-t border-slate-100 dark:border-slate-800" :class="notification.read_at ? '' : 'bg-slate-50 dark:bg-slate-800/40'">
                                                <div class="flex items-start gap-3">
                                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                                        <span x-text="notification.icon"></span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100" x-text="notification.title"></p>
                                                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300" x-text="notification.message"></p>
                                                        <div class="mt-3 flex items-center justify-between gap-3">
                                                            <span class="text-xs text-slate-400 dark:text-slate-500" x-text="notification.created_at"></span>
                                                            <a
                                                                :href="notification.action_url"
                                                                class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white hover:bg-slate-700"
                                                                x-text="notification.action_label"
                                                            ></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div x-data="{ userOpen: false }" @click.outside="userOpen = false" class="relative">
                                <button
                                    type="button"
                                    @click="userOpen = !userOpen"
                                    class="inline-flex items-center gap-3 rounded-full border border-slate-200 bg-white px-2 py-1.5 text-left shadow-sm transition hover:border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-slate-600"
                                >
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white dark:bg-slate-700">
                                        {{ $userInitials }}
                                    </span>
                                    <span class="hidden text-sm font-medium text-slate-700 sm:block dark:text-slate-100">{{ auth()->user()->name }}</span>
                                    <svg class="h-4 w-4 text-slate-500 dark:text-slate-300" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div
                                    x-show="userOpen"
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute right-0 z-30 mt-2 w-52 origin-top-right rounded-xl border border-slate-200 bg-white p-1 shadow-lg dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <a
                                        href="{{ route('profile.edit') }}"
                                        class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 2a4 4 0 100 8 4 4 0 000-8zM3 15a7 7 0 1114 0v1H3v-1z" />
                                        </svg>
                                        Profile
                                    </a>
                                    <div class="my-1 border-t border-slate-200 dark:border-slate-700"></div>
                                    <button
                                        type="button"
                                        @click.prevent="document.getElementById('logout-form').submit()"
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:hover:bg-red-900/20"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M3 4.75A1.75 1.75 0 014.75 3h6.5A1.75 1.75 0 0113 4.75v1.5a.75.75 0 01-1.5 0v-1.5a.25.25 0 00-.25-.25h-6.5a.25.25 0 00-.25.25v10.5c0 .138.112.25.25.25h6.5a.25.25 0 00.25-.25v-1.5a.75.75 0 011.5 0v1.5A1.75 1.75 0 0111.25 17h-6.5A1.75 1.75 0 013 15.25V4.75z" clip-rule="evenodd" />
                                            <path fill-rule="evenodd" d="M10.22 7.22a.75.75 0 011.06 0l2.25 2.25a.75.75 0 010 1.06l-2.25 2.25a.75.75 0 11-1.06-1.06l.97-.97H7a.75.75 0 010-1.5h4.19l-.97-.97a.75.75 0 010-1.06z" clip-rule="evenodd" />
                                        </svg>
                                        Logout
                                    </button>
                                </div>
                            </div>

                            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                                @csrf
                            </form>
                        @endauth
                    </div>
                </header>

                <main class="flex-1 bg-slate-50 px-4 py-6 lg:px-10 lg:py-10 dark:bg-slate-950">
                    <div class="max-w-6xl mx-auto">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
        @php
            $alertMessage = session('status') ?? session('success') ?? session('message') ?? session('error');
            $alertIcon = session('error') ? 'error' : 'success';
            $alertTitle = $alertIcon === 'error' ? 'Oops!' : 'All set!';
            $validationErrors = $errors->any() ? $errors->all() : [];
            $validationList = '';

            if (!empty($validationErrors)) {
                $validationList = '<ul class="text-left text-sm">' .
                    collect($validationErrors)->map(fn ($message) => "<li>$message</li>")->implode('') .
                    '</ul>';
            }
        @endphp

        <script>
            function layoutShell() {
                return {
                    sidebarOpen: false,
                    theme: 'light',
                    initTheme() {
                        this.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                    },
                    toggleTheme() {
                        if (this.theme === 'dark') {
                            this.theme = 'light';
                            localStorage.setItem('theme', 'light');
                            document.documentElement.classList.remove('dark');
                            return;
                        }

                        this.theme = 'dark';
                        localStorage.setItem('theme', 'dark');
                        document.documentElement.classList.add('dark');
                    },
                };
            }

            window.notifyToast = function ({ icon = 'success', title = '', text = '' } = {}) {
                if (!window.Swal) {
                    return;
                }

                window.swalFire({
                    icon,
                    title,
                    text,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true,
                });
            };

            window.addEventListener('app:toast', (event) => {
                window.notifyToast(event.detail || {});
            });

            document.addEventListener('submit', (event) => {
                if (event.defaultPrevented) {
                    return;
                }

                const form = event.target;
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                if (form.dataset.preventDoubleSubmit === 'false') {
                    return;
                }

                const button = form.querySelector('button[type=\"submit\"], input[type=\"submit\"]');
                if (!button || button.disabled) {
                    return;
                }

                button.disabled = true;

                const loadingText = button.dataset.loadingText;
                if (loadingText && button.tagName.toLowerCase() === 'button') {
                    button.dataset.originalText = button.innerHTML;
                    button.innerHTML = loadingText;
                }
            });
        </script>

        @if ($validationErrors)
            <script>
                window.dispatchSwal({
                    icon: 'error',
                    title: 'Validation failed',
                    html: @json($validationList),
                    confirmButtonText: 'Fix it',
                });
            </script>
        @elseif ($alertMessage)
            <script>
                window.notifyToast({
                    icon: '{{ $alertIcon }}',
                    title: @json($alertTitle),
                    text: @json(__($alertMessage)),
                });
            </script>
        @endif

        @livewireScripts
    </body>
</html>
