@extends('layouts.mahasiswa')

@section('title', __('nav.home'))

@section('content')
    <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('dashboard.greeting', ['name' => Auth::user()->name]) }} 👋</h1>
            <p class="mt-1 text-gray-500">{{ __('dashboard.subtitle') }}</p>
        </div>
        <x-ui-button variant="primary" :href="route('mahasiswa.pendaftaran.create')" icon="plus">{{ __('dashboard.register_now') }}</x-ui-button>
    </div>

    @forelse ($pendaftaran as $p)
        <a href="{{ route('mahasiswa.pendaftaran.show', $p) }}"
           class="mb-4 block rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md hover:ring-indigo-200">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <x-icon name="document" class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="font-mono text-sm font-semibold text-gray-900">{{ $p->nomor_pendaftaran }}</div>
                        <div class="mt-0.5 text-sm text-gray-500">{{ $p->jalur?->nama }} · {{ $p->gelombang?->nama ?? '—' }} · {{ $p->tahun?->nama }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <x-ui-status-badge :status="$p->status" />
                    <svg class="h-5 w-5 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
            </div>
        </a>
    @empty
        <div class="rounded-xl bg-white p-12 text-center shadow-sm ring-1 ring-gray-200">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50">
                <x-icon name="document" class="h-8 w-8 text-indigo-400" />
            </div>
            <h2 class="mt-4 text-lg font-semibold text-gray-900">{{ __('dashboard.empty_title') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('dashboard.empty_subtitle') }}</p>
            <div class="mt-6">
                <x-ui-button variant="primary" :href="route('mahasiswa.pendaftaran.create')" icon="plus">{{ __('dashboard.register_now') }}</x-ui-button>
            </div>
        </div>
    @endforelse
@endsection
