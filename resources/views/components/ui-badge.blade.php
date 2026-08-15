@props(['color' => 'gray'])

@php
    $colors = [
        'gray' => 'bg-gray-100 text-gray-700 ring-gray-500/20',
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'red' => 'bg-red-50 text-red-700 ring-red-600/20',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'blue' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ' . $colors[$color]]) }}>
    {{ $slot }}
</span>
