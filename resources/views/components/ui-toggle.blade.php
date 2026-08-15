@props(['name', 'id' => null, 'checked' => false, 'label' => null, 'value' => '1', 'description' => null])

<label @if ($id) for="{{ $id }}" @endif class="inline-flex cursor-pointer select-none items-center gap-3">
    <input type="checkbox"
           name="{{ $name }}"
           @if ($id) id="{{ $id }}" @endif
           value="{{ $value }}"
           @if ($checked) checked @endif
           {{ $attributes->except(['class']) }}
           class="peer sr-only">
    <span class="relative h-6 w-11 shrink-0 rounded-full bg-gray-200 transition-colors after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform after:content-[''] peer-checked:bg-indigo-600 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-600 peer-focus-visible:ring-offset-2 peer-checked:after:translate-x-5"></span>
    @if ($label || $description)
        <span>
            @if ($label)
                <span class="block text-sm font-medium text-gray-900">{{ $label }}</span>
            @endif
            @if ($description)
                <span class="block text-xs text-gray-500">{{ $description }}</span>
            @endif
        </span>
    @endif
</label>
