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
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-inter bg-slate-50 text-slate-900">
        @php
            $businessLogo = setting('business_logo') ?: setting('logo');
            $businessName = setting('business_name', 'Invoice Portal');
            $businessInitial = strtoupper(substr($businessName, 0, 2));
            $businessGstin = setting('gstin', '');
            $stateName = setting('state');
            $dueDays = setting('default_due_days', 15);
        @endphp
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
                    <div class="flex items-center gap-3">
            
                        <div>
                            <p class="text-sm uppercase tracking-[0.2em] text-slate-200">{{ $businessName }}</p>
                            <p class="text-xs text-white/80">GSTIN: {{ $businessGstin }}</p>
                        </div>
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
                            ['label' => 'Items', 'route' => 'products.index', 'pattern' => 'products.*'],
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
                    @if($stateName)
                        <p class="text-xs uppercase tracking-[0.3em] text-white/70">State</p>
                        <p class="text-sm font-semibold">{{ $stateName }}</p>
                    @endif
                    <p class="text-xs text-white/60">Due days: {{ $dueDays }}</p>
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
                            <p class="text-xs uppercase tracking-widest text-slate-400">{{ setting('invoice_prefix', config('invoice.invoice_prefix', 'INV')) }} Portal</p>
                            <h1 class="text-2xl font-semibold text-slate-900">@yield('page-title', 'Dashboard')</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @hasSection('primary-action')
                            @yield('primary-action')
                        @endif

                        @auth
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
                                    class="relative inline-flex items-center justify-center h-11 w-11 rounded-full border border-slate-200 bg-white text-slate-600 hover:text-slate-900 transition"
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
                                    class="absolute right-0 mt-3 w-[360px] origin-top-right divide-y divide-slate-200 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl"
                                >
                                    <div class="px-4 py-4 flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">Notifications</p>
                                            <p class="text-xs text-slate-500">Latest alerts for collection actions</p>
                                        </div>
                                        <button
                                            type="button"
                                            class="text-xs font-semibold text-slate-500 hover:text-slate-900"
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
                                            <div class="px-4 py-4 border-t border-slate-100" :class="notification.read_at ? '' : 'bg-slate-50'">
                                                <div class="flex items-start gap-3">
                                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                                                        <span x-text="notification.icon"></span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-semibold text-slate-900" x-text="notification.title"></p>
                                                        <p class="mt-1 text-sm text-slate-600" x-text="notification.message"></p>
                                                        <div class="mt-3 flex items-center justify-between gap-3">
                                                            <span class="text-xs text-slate-400" x-text="notification.created_at"></span>
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
            window.notifyToast = function ({ icon = 'success', title = '', text = '' } = {}) {
                if (!window.Swal) {
                    return;
                }

                Swal.fire({
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
                Swal.fire({
                    icon: 'error',
                    title: 'Validation failed',
                    html: @json($validationList),
                    confirmButtonText: 'Fix it'
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
