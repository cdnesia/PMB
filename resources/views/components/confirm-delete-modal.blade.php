<div
    x-data="{
        show: false,
        title: 'Konfirmasi Hapus',
        message: 'Yakin ingin menghapus data ini? Tindakan ini tidak bisa dibatalkan.',
        pendingForm: null,
        open(detail) {
            this.pendingForm = detail.form;
            this.title = detail.title || 'Konfirmasi Hapus';
            this.message = detail.message || 'Yakin ingin menghapus data ini? Tindakan ini tidak bisa dibatalkan.';
            this.show = true;
        },
        confirm() {
            this.pendingForm?.submit();
            this.show = false;
        },
    }"
    x-on:confirm-delete.window="open($event.detail)"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
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
        class="relative mb-6 transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:mx-auto sm:w-full sm:max-w-md"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                    <x-icon name="warning" class="h-5 w-5 text-red-600" />
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900" x-text="title"></h2>
                    <p class="mt-1 text-sm text-gray-600" x-text="message"></p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-ui-button variant="secondary" type="button" x-on:click="show = false">Batal</x-ui-button>
                <x-ui-button variant="danger" type="button" x-on:click="confirm()">Hapus</x-ui-button>
            </div>
        </div>
    </div>
</div>
