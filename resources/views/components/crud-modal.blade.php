@props(['createTitle', 'editTitle' => null, 'size' => 'lg'])

@php
    $sizes = [
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
    ];
@endphp

<div
    x-show="show"
    x-cloak
    x-on:keydown.escape.window="show = false"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
    style="display: none"
>
    <div
        x-show="show"
        x-on:click="show = false"
        class="fixed inset-0 bg-gray-500 opacity-75 transition-all"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-75"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-75"
        x-transition:leave-end="opacity-0"
    ></div>

    <div
        x-show="show"
        class="relative mb-6 transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:mx-auto sm:w-full {{ $sizes[$size] ?? $sizes['lg'] }}"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        <form x-on:submit.prevent="submit()">
            <div class="max-h-[75vh] overflow-y-auto p-6">
                <h2 class="text-base font-semibold text-gray-900" x-text="mode === 'edit' ? '{{ $editTitle ?? $createTitle }}' : '{{ $createTitle }}'"></h2>

                <div class="mt-4 space-y-4">
                    {{ $slot }}
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4">
                <x-ui-button variant="secondary" type="button" x-on:click="show = false" x-bind:disabled="submitting">Batal</x-ui-button>
                <x-ui-button variant="primary" type="submit" x-bind:disabled="submitting" icon="check">
                    <span x-show="!submitting">Simpan</span>
                    <span x-show="submitting">Menyimpan...</span>
                </x-ui-button>
            </div>
        </form>
    </div>
</div>
