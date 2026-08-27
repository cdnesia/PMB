<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div x-data="{ sidebarOpen: false }">
            {{-- Mobile overlay --}}
            <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
                 class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden" x-transition.opacity></div>

            {{-- Sidebar --}}
            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                   class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col bg-slate-900 transition-transform duration-200 lg:translate-x-0">
                <div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-800 px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600">
                        <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white leading-tight">PMB Panel</div>
                        <div class="text-[11px] text-slate-400">Penerimaan Mahasiswa Baru</div>
                    </div>
                </div>

                {{-- User login (di atas menu) --}}
                <div class="shrink-0 border-b border-slate-800 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-700 text-sm font-semibold text-white">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-sm font-medium text-white">{{ Auth::user()->name }}</div>
                            <div class="truncate text-xs text-slate-400 capitalize">{{ Auth::user()->getRoleNames()->first() }}</div>
                        </div>
                    </div>
                </div>

                @php
                    $navGroups = [
                        [
                            'label' => 'Pendaftaran',
                            'items' => [
                                'Pendaftar' => ['icon' => 'user', 'route' => 'admin.pendaftar.index', 'match' => 'admin.pendaftar.*'],
                            ],
                        ],
                        [
                            'label' => 'Tes CBT',
                            'items' => array_filter([
                                'Bank Soal' => Auth::user()->can('kelola-cbt') ? ['icon' => 'cbt', 'route' => 'admin.cbt-soal.index', 'match' => 'admin.cbt-soal.*'] : null,
                                'Jadwal CBT' => Auth::user()->can('kelola-cbt') ? ['icon' => 'calendar', 'route' => 'admin.cbt-jadwal.index', 'match' => ['admin.cbt-jadwal.*', 'admin.cbt-peserta.*']] : null,
                            ]),
                        ],
                        [
                            'label' => 'Pengaturan',
                            'items' => [
                                'Tahun Penerimaan' => ['icon' => 'calendar', 'route' => 'admin.tahun.index', 'match' => 'admin.tahun.*'],
                                'Gelombang' => ['icon' => 'adjust', 'route' => 'admin.gelombang.index', 'match' => 'admin.gelombang.*'],
                                'Jalur & Biaya' => ['icon' => 'route', 'route' => 'admin.jalur.index', 'match' => 'admin.jalur.*'],
                                'Program Studi' => ['icon' => 'academic', 'route' => 'admin.prodi.index', 'match' => 'admin.prodi.*'],
                                'Kelas Perkuliahan' => ['icon' => 'square-stack', 'route' => 'admin.kelas.index', 'match' => 'admin.kelas.*'],
                                'Kuota Prodi' => ['icon' => 'chart', 'route' => 'admin.kuota.index', 'match' => 'admin.kuota.*'],
                                'Promo' => ['icon' => 'credit-card', 'route' => 'admin.promo.index', 'match' => 'admin.promo.*'],
                                'Setting Prodi' => ['icon' => 'adjust', 'route' => 'admin.setting-prodi.index', 'match' => 'admin.setting-prodi.*'],
                                'Dokumen Persyaratan' => ['icon' => 'document', 'route' => 'admin.dokumen.index', 'match' => 'admin.dokumen.*'],
                            ],
                        ],
                        [
                            'label' => 'Laporan',
                            'items' => [
                                'Rekap Pendaftaran' => ['icon' => 'chart', 'route' => 'admin.laporan.index', 'match' => 'admin.laporan.*'],
                                'Rekap Referrer' => ['icon' => 'chart', 'route' => 'admin.referrer.index', 'match' => 'admin.referrer.*'],
                            ],
                        ],
                        [
                            'label' => 'Sistem',
                            'items' => array_filter([
                                'Manajemen User' => Auth::user()->can('kelola-user') ? ['icon' => 'user', 'route' => 'admin.user.index', 'match' => 'admin.user.*'] : null,
                                'Pengaturan Umum' => ['icon' => 'adjust', 'route' => 'admin.pengaturan.index', 'match' => 'admin.pengaturan.*'],
                            ]),
                        ],
                    ];
                @endphp

                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                    {{-- Dashboard (selalu di atas, tanpa grup) --}}
                    <a href="{{ route('admin.dashboard') }}"
                       class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                              {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <x-icon name="dashboard" class="h-5 w-5 shrink-0" />
                        Dashboard
                        @if (request()->routeIs('admin.dashboard'))
                            <span class="ml-auto h-1.5 w-1.5 rounded-full bg-white/80"></span>
                        @endif
                    </a>

                    @foreach ($navGroups as $group)
                        <div class="px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            {{ $group['label'] }}
                        </div>
                        @foreach ($group['items'] as $label => $item)
                            <a href="{{ route($item['route']) }}"
                               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                                      {{ request()->routeIs(...(array) $item['match']) ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <x-icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                                {{ $label }}
                                @if (request()->routeIs(...(array) $item['match']))
                                    <span class="ml-auto h-1.5 w-1.5 rounded-full bg-white/80"></span>
                                @endif
                            </a>
                        @endforeach
                    @endforeach
                </nav>

            </aside>

            {{-- Main --}}
            <div class="lg:pl-72">
                {{-- Top bar --}}
                <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-gray-200 bg-white/90 px-4 backdrop-blur sm:px-6">
                    <button type="button" @click="sidebarOpen = true" class="rounded-md p-1.5 text-gray-500 hover:bg-gray-100 lg:hidden">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <h2 class="truncate text-sm font-semibold text-gray-900">@yield('title', 'Dashboard')</h2>
                        <p class="hidden text-xs text-gray-500 sm:block">Sistem Penerimaan Mahasiswa Baru</p>
                    </div>

                    <div class="ml-auto flex items-center gap-2" x-data="{ open: false }">
                        <div class="relative">
                            <button type="button" @click="open = !open"
                                class="flex items-center gap-2 rounded-full py-1 pl-1 pr-2 transition hover:bg-gray-100">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-sm font-semibold text-white">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                                <span class="hidden text-sm font-medium text-gray-700 sm:block">{{ Auth::user()->name }}</span>
                                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div x-show="open" x-cloak @click.outside="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute right-0 mt-2 w-56 origin-top-right rounded-lg bg-white py-1 shadow-lg ring-1 ring-black/5">
                                <div class="border-b border-gray-100 px-4 py-2.5">
                                    <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                                    <div class="truncate text-xs text-gray-500">{{ Auth::user()->email }}</div>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <x-icon name="user" class="h-4 w-4 text-gray-400" /> Profil
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <x-icon name="logout" class="h-4 w-4" /> Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="p-4 sm:p-6 lg:p-8">
                    @yield('content')
                </main>
            </div>

            {{-- Notifications --}}
            @php
                $toasts = [];
                if (session('success')) $toasts[] = ['type' => 'success', 'message' => session('success')];
                if (session('error')) $toasts[] = ['type' => 'error', 'message' => session('error')];
                if (session('info')) $toasts[] = ['type' => 'info', 'message' => session('info')];
                if (session('warning')) $toasts[] = ['type' => 'warning', 'message' => session('warning')];
                if ($errors->any()) {
                    foreach ($errors->all() as $error) {
                        $toasts[] = ['type' => 'error', 'message' => $error];
                    }
                }
            @endphp
            <x-toast :items="$toasts" />

            <x-confirm-delete-modal />
        </div>

        @stack('scripts')
    </body>
</html>
