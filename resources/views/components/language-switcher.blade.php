@php
    $languages = [
        'id' => ['label' => 'Bahasa Indonesia', 'short' => 'ID'],
        'en' => ['label' => 'English', 'short' => 'EN'],
        'ar' => ['label' => 'العربية', 'short' => 'AR'],
        'zh' => ['label' => '中文', 'short' => '中文'],
    ];
    $current = $languages[app()->getLocale()] ?? $languages['id'];
@endphp

<div x-data="{ open: false }" class="relative">
    <button type="button" @click="open = !open"
        class="flex items-center gap-1.5 rounded-lg px-2.5 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100">
        <x-icon name="globe" class="h-4 w-4 text-gray-400" />
        {{ $current['short'] }}
        <svg class="h-3.5 w-3.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div x-show="open" x-cloak @click.outside="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="absolute right-0 z-30 mt-2 w-44 origin-top-right rounded-lg bg-white py-1 shadow-lg ring-1 ring-black/5">
        @foreach ($languages as $code => $lang)
            <a href="{{ route('locale.set', $code) }}"
               class="flex items-center justify-between px-4 py-2 text-sm {{ app()->getLocale() === $code ? 'font-semibold text-indigo-600' : 'text-gray-700 hover:bg-gray-50' }}">
                {{ $lang['label'] }}
                @if (app()->getLocale() === $code)
                    <x-icon name="check" class="h-4 w-4" />
                @endif
            </a>
        @endforeach
    </div>
</div>
