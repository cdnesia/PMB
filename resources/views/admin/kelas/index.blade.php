@extends('layouts.admin')

@section('title', 'Kelas Perkuliahan')

@section('content')
    <div x-data="crudModal({
        storeUrl: @js(route('admin.kelas.store')),
        defaults: { kode: '', nama: '', is_active: true },
    })">
        <x-ui-page-header title="Kelas Perkuliahan" description="Kelola kelas perkuliahan (Reguler A, Reguler B, Kelas Karyawan, dll).">
            <x-slot:action>
                <x-ui-button variant="primary" type="button" x-on:click="openCreate()" icon="plus">Tambah Kelas</x-ui-button>
            </x-slot:action>
        </x-ui-page-header>

        <div id="crud-table">
            <x-ui-card :padding="''">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Kode</th>
                                <th class="px-6 py-3">Nama</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($kelas as $k)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $k->kode }}</td>
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $k->nama }}</td>
                                    <td class="px-6 py-3">
                                        <x-ui-badge :color="$k->is_active ? 'green' : 'gray'">{{ $k->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button"
                                                x-on:click="openEdit(@js([
                                                    'id' => $k->id,
                                                    '_updateUrl' => route('admin.kelas.update', $k),
                                                    'kode' => $k->kode,
                                                    'nama' => $k->nama,
                                                    'is_active' => (bool) $k->is_active,
                                                ]))"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </button>
                                            <button type="button"
                                                x-on:click="$dispatch('confirm-delete', { url: @js(route('admin.kelas.destroy', $k)), message: @js('Hapus kelas \''.$k->nama.'\'? Tindakan ini tidak bisa dibatalkan.') })"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-ui-empty-state :colspan="4" message="Belum ada kelas perkuliahan." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($kelas->hasPages())
                    <div class="border-t border-gray-100 px-6 py-3">{{ $kelas->links() }}</div>
                @endif
            </x-ui-card>
        </div>

        <x-crud-modal create-title="Tambah Kelas" edit-title="Edit Kelas">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-ui-label for="modal-kode" required>Kode</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="kode" id="modal-kode" x-model="form.kode" placeholder="REG-A" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-nama" required>Nama</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="modal-nama" x-model="form.nama" placeholder="Reguler A" />
                    </div>
                </div>
            </div>

            <x-ui-toggle name="is_active" id="modal-is_active" x-model="form.is_active" label="Aktif" description="Kelas dapat dipilih oleh pendaftar." />
        </x-crud-modal>
    </div>
@endsection
