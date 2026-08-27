<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $seoTitle = \App\Models\Pengaturan::get('seo_title') ?: config('app.name').' — Penerimaan Mahasiswa Baru';
            $seoDescription = \App\Models\Pengaturan::get('seo_description');
            $seoKeywords = \App\Models\Pengaturan::get('seo_keywords');
            $seoAuthor = \App\Models\Pengaturan::get('seo_author');
        @endphp

        <title>{{ $seoTitle }}</title>
        @if ($seoDescription)
            <meta name="description" content="{{ $seoDescription }}">
        @endif
        @if ($seoKeywords)
            <meta name="keywords" content="{{ $seoKeywords }}">
        @endif
        @if ($seoAuthor)
            <meta name="author" content="{{ $seoAuthor }}">
        @endif
        <meta property="og:title" content="{{ $seoTitle }}">
        @if ($seoDescription)
            <meta property="og:description" content="{{ $seoDescription }}">
        @endif
        <meta property="og:type" content="website">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        <div class="absolute end-4 top-4 z-10">
            <x-language-switcher />
        </div>

        <div class="flex min-h-screen">
            {{-- Branding panel (desktop) --}}
            <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-slate-900 p-12 lg:flex">
                <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-indigo-600/30 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-indigo-500/20 blur-3xl"></div>

                <a href="/" class="relative flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-600">
                        <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </span>
                    <span class="text-xl font-bold text-white">PMB</span>
                </a>

                <div class="relative">
                    <h1 class="text-3xl font-bold leading-tight text-white">
                        {!! __('guest.brand_title') !!}
                    </h1>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-300">
                        {{ __('guest.brand_subtitle') }}
                    </p>

                    <ul class="mt-8 space-y-3">
                        @php
                            $features = [
                                __('guest.feature_1'),
                                __('guest.feature_2'),
                                __('guest.feature_3'),
                            ];
                        @endphp
                        @foreach ($features as $feature)
                            <li class="flex items-start gap-3 text-sm text-slate-300">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-indigo-500/20">
                                    <svg class="h-3 w-3 text-indigo-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </span>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <p class="relative text-xs text-slate-500">© {{ date('Y') }} {{ config('app.name') }}. {{ __('guest.all_rights_reserved') }}</p>
            </div>

            {{-- Form panel --}}
            <div class="flex flex-1 flex-col justify-center px-4 py-10 sm:px-8 lg:px-16 lg:py-12">
                <div class="mx-auto w-full sm:max-w-sm">
                    {{-- Branding (mobile & tablet) --}}
                    <a href="/" class="mb-8 flex flex-col items-center gap-3 text-center lg:hidden">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 shadow-lg shadow-indigo-600/30">
                            <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                            </svg>
                        </span>
                        <span>
                            <span class="block text-xl font-bold text-gray-900">PMB</span>
                            <span class="block text-xs text-gray-500">{{ __('guest.tagline') }}</span>
                        </span>
                    </a>

                    {{-- Form card (mobile & tablet) --}}
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200 sm:p-8 lg:bg-transparent lg:p-0 lg:shadow-none lg:ring-0">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
