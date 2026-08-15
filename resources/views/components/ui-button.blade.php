@props([
    'variant' => 'primary',
    'type' => null,
    'size' => 'md',
    'icon' => null,
    'href' => null,
])

@php
    $variants = [
        'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700 focus-visible:outline-indigo-600',
        'secondary' => 'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline-gray-600',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus-visible:outline-red-600',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:outline-emerald-600',
    ];

    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
    ];

    $iconSizes = [
        'sm' => 'h-3.5 w-3.5',
        'md' => 'h-4 w-4',
        'lg' => 'h-5 w-5',
    ];

    $classes = 'inline-flex items-center justify-center gap-2 rounded-lg font-semibold shadow-sm transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50 disabled:cursor-not-allowed ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-icon :name="$icon" class="{{ $iconSizes[$size] }}" />
        @endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge([
        'type' => $type ?? 'submit',
        'class' => $classes,
    ]) }}>
        @if ($icon)
            <x-icon :name="$icon" class="{{ $iconSizes[$size] }}" />
        @endif
        {{ $slot }}
    </button>
@endif
