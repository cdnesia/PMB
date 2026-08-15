<section>
    <header class="mb-6">
        <h2 class="text-lg font-semibold text-red-600">Hapus Akun</h2>
        <p class="mt-1 text-sm text-gray-500">
            Setelah akun dihapus, semua data akan dihapus permanen. Pastikan Anda telah mengunduh data yang ingin disimpan.
        </p>
    </header>

    <x-ui-button
        variant="danger"
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        <x-icon name="trash" class="h-4 w-4" /> Hapus Akun
    </x-ui-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-gray-900">
                Yakin ingin menghapus akun Anda?
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Setelah akun dihapus, semua data akan dihapus permanen. Masukkan password untuk konfirmasi penghapusan permanen.
            </p>

            <div class="mt-6">
                <x-ui-label for="password" required>Password</x-ui-label>
                <div class="mt-2">
                    <x-ui-input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Password"
                    />
                </div>
                @error('password', 'userDeletion')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-ui-button variant="secondary" type="button" x-on:click="$dispatch('close')">Batal</x-ui-button>
                <x-ui-button variant="danger">Hapus Akun</x-ui-button>
            </div>
        </form>
    </x-modal>
</section>
