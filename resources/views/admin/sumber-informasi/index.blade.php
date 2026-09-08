@extends('layouts.admin')

@section('title', 'Sumber Informasi')

@section('content')
    <div x-data="crudModal({
        storeUrl: @js(route('admin.sumber-informasi.store')),
        defaults: { kode: '', nama: '', urutan: 0, is_active: true },
    })">
        <x-ui-page-header title="Sumber Informasi" description="Kelola pilihan 'Dari mana Anda tahu tentang UM Jambi?' yang tampil saat registrasi akun.">
            <x-slot:action>
                <x-ui-button variant="primary" type="button" x-on:click="openCreate()" icon="plus">Tambah Sumber</x-ui-button>
            </x-slot:action>
        </x-ui-page-header>

        <div id="crud-table">
            <x-ui-card :padding="''">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Urutan</th>
                                <th class="px-6 py-3">Kode</th>
                                <th class="px-6 py-3">Nama</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($sumberInformasi as $s)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 text-xs text-gray-500">{{ $s->urutan }}</td>
                                    <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $s->kode }}</td>
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $s->nama }}</td>
                                    <td class="px-6 py-3">
                                        <x-ui-badge :color="$s->is_active ? 'green' : 'gray'">{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button"
                                                x-on:click="openEdit(@js([
                                                    'id' => $s->id,
                                                    '_updateUrl' => route('admin.sumber-informasi.update', $s),
                                                    'kode' => $s->kode,
                                                    'nama' => $s->nama,
                                                    'urutan' => $s->urutan,
                                                    'is_active' => (bool) $s->is_active,
                                                ]))"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </button>
                                            <button type="button"
                                                x-on:click="$dispatch('confirm-delete', { url: @js(route('admin.sumber-informasi.destroy', $s)), message: @js('Hapus sumber informasi \''.$s->nama.'\'? Tindakan ini tidak bisa dibatalkan.') })"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-ui-empty-state :colspan="5" message="Belum ada sumber informasi." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($sumberInformasi->hasPages())
                    <div class="border-t border-gray-100 px-6 py-3">{{ $sumberInformasi->links() }}</div>
                @endif
            </x-ui-card>
        </div>

        <x-crud-modal create-title="Tambah Sumber Informasi" edit-title="Edit Sumber Informasi">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-ui-label for="modal-kode" required>Kode</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="kode" id="modal-kode" x-model="form.kode" placeholder="INSTAGRAM" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-nama" required>Nama</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="modal-nama" x-model="form.nama" placeholder="Instagram" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-urutan">Urutan</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" name="urutan" id="modal-urutan" x-model.number="form.urutan" min="0" />
                    </div>
                </div>
            </div>

            <x-ui-toggle name="is_active" id="modal-is_active" x-model="form.is_active" label="Aktif" description="Tampil sebagai pilihan saat registrasi." />
        </x-crud-modal>
    </div>
@endsection
