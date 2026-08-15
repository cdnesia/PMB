@props(['for' => null, 'value' => null, 'required' => false])

<label {{ $attributes->merge(['for' => $for, 'class' => 'block text-sm font-medium leading-6 text-gray-900']) }}>
    {{ $value ?? $slot }}
    @if ($required)
        <span class="text-red-500">*</span>
    @endif
</label>
