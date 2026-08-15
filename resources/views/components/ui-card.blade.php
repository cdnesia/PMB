@props(['padding' => 'p-6'])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-sm ring-1 ring-gray-200 ' . $padding]) }}>
    {{ $slot }}
</div>
