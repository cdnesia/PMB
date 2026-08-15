@props(['name', 'id' => null, 'checked' => false, 'label' => null, 'value' => '1'])

<label class="inline-flex cursor-pointer select-none items-center gap-2.5">
    <input type="checkbox"
           name="{{ $name }}"
           @if ($id) id="{{ $id }}" @endif
           value="{{ $value }}"
           @if ($checked) checked @endif
           {{ $attributes->except(['class']) }}
           class="peer sr-only">
    <span class="flex h-5 w-5 items-center justify-center rounded-md border border-gray-300 bg-white text-white transition-colors peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-600 peer-focus-visible:ring-offset-2">
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
    </span>
    @if ($label)
        <span class="text-sm text-gray-700">{{ $label }}</span>
    @endif
</label>
