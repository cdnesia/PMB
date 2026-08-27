<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'PMB') · {{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="flex min-h-screen flex-col">
            <header class="sticky top-0 z-20 border-b border-gray-200 bg-white/90 backdrop-blur">
                <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                    <div class="flex items-center gap-8">
                        <a href="{{ route('mahasiswa.dashboard') }}" class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600">
                                <svg class="h-4.5 w-4.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                                </svg>
                            </span>
                            <span class="text-base font-bold text-gray-900">PMB Portal</span>
                        </a>
                        <nav class="hidden gap-1 text-sm sm:flex">
                            <a href="{{ route('mahasiswa.dashboard') }}"
                               class="rounded-lg px-3 py-2 font-medium transition {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">{{ __('nav.home') }}</a>
                            <a href="{{ route('mahasiswa.pendaftaran.index') }}"
                               class="rounded-lg px-3 py-2 font-medium transition {{ request()->routeIs('mahasiswa.pendaftaran.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">{{ __('nav.my_registration') }}</a>
                            <a href="{{ route('mahasiswa.cbt.index') }}"
                               class="rounded-lg px-3 py-2 font-medium transition {{ request()->routeIs('mahasiswa.cbt.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">{{ __('nav.cbt_test') }}</a>
                        </nav>
                    </div>

                    <div class="flex items-center gap-1">
                        <x-language-switcher />

                        <div x-data="{ open: false }" class="relative">
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
                                 class="absolute end-0 mt-2 w-56 origin-top-right rounded-lg bg-white py-1 shadow-lg ring-1 ring-black/5">
                                <div class="border-b border-gray-100 px-4 py-2.5">
                                    <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                                    <div class="truncate text-xs text-gray-500">{{ Auth::user()->email }}</div>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <x-icon name="user" class="h-4 w-4 text-gray-400" /> {{ __('nav.profile') }}
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <x-icon name="logout" class="h-4 w-4" /> {{ __('nav.logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6">
                @yield('content')
            </main>

            <footer class="border-t border-gray-200 py-6">
                <p class="text-center text-xs text-gray-400">© {{ date('Y') }} {{ config('app.name') }} — {{ __('nav.footer_tagline') }}</p>
            </footer>

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
        </div>

        @stack('scripts')
    </body>
</html>
