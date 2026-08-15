@props(['items' => []])

<div
    x-data="toast(@js($items))"
    @notify.window="push($event.detail)"
    class="pointer-events-none fixed bottom-4 right-4 z-[100] flex w-full max-w-sm flex-col gap-3 px-4 sm:px-0"
>
    <template x-for="item in items" :key="item.id">
        <div
            x-show="item.show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="pointer-events-auto relative flex items-start gap-3 overflow-hidden rounded-xl bg-white p-4 pr-3 shadow-lg ring-1 ring-black/5"
        >
            <span class="absolute inset-y-0 left-0 w-1" :class="typeClasses(item.type).bar"></span>
            <span class="shrink-0" :class="typeClasses(item.type).icon" x-html="icon(item.type)"></span>
            <p class="flex-1 pt-0.5 text-sm font-medium leading-snug text-gray-800" x-text="item.message"></p>
            <button type="button" @click="dismiss(item.id)"
                class="shrink-0 rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>
</div>
