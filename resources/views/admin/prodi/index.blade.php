@extends('layouts.admin')

@section('title', 'Program Studi')

@section('content')
    <div x-data="crudModal({
        storeUrl: @js(route('admin.prodi.store')),
        defaults: { kode: '', nama: '', jenjang: 'S1', fakultas: '', is_active: true },
    })">
        <x-ui-page-header title="Program Studi" description="Kelola program studi yang tersedia dalam penerimaan.">
            <x-slot:action>
                <x-ui-button variant="primary" type="button" x-on:click="openCreate()" icon="plus">Tambah Prodi</x-ui-button>
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
                                <th class="px-6 py-3">Jenjang</th>
                                <th class="px-6 py-3">Fakultas</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($prodi as $p)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $p->kode }}</td>
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $p->nama }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $p->jenjang }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $p->fakultas ?? '-' }}</td>
                                    <td class="px-6 py-3">
                                        <x-ui-badge :color="$p->is_active ? 'green' : 'gray'">{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button"
                                                x-on:click="openEdit(@js([
                                                    'id' => $p->id,
                                                    '_updateUrl' => route('admin.prodi.update', $p),
                                                    'kode' => $p->kode,
                                                    'nama' => $p->nama,
                                                    'jenjang' => $p->jenjang,
                                                    'fakultas' => $p->fakultas,
                                                    'is_active' => (bool) $p->is_active,
                                                ]))"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </button>
                                            <button type="button"
                                                x-on:click="$dispatch('confirm-delete', { url: @js(route('admin.prodi.destroy', $p)), message: @js('Hapus prodi \''.$p->nama.'\'? Tindakan ini tidak bisa dibatalkan.') })"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-ui-empty-state :colspan="6" message="Belum ada program studi." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($prodi->hasPages())
                    <div class="border-t border-gray-100 px-6 py-3">{{ $prodi->links() }}</div>
                @endif
            </x-ui-card>
        </div>

        <x-crud-modal create-title="Tambah Prodi" edit-title="Edit Prodi">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-ui-label for="modal-kode" required>Kode</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="kode" id="modal-kode" x-model="form.kode" placeholder="TI" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-nama" required>Nama Program Studi</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="modal-nama" x-model="form.nama" placeholder="Teknik Informatika" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-jenjang" required>Jenjang</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="jenjang" id="modal-jenjang" x-model="form.jenjang" :searchable="false">
                            @foreach (['D3', 'D4', 'S1', 'S2'] as $j)
                                <option value="{{ $j }}">{{ $j }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-fakultas">Fakultas</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="fakultas" id="modal-fakultas" x-model="form.fakultas" placeholder="Teknik" />
                    </div>
                </div>
            </div>

            <x-ui-toggle name="is_active" id="modal-is_active" x-model="form.is_active" label="Aktif" description="Prodi dapat dipilih oleh pendaftar." />
        </x-crud-modal>
    </div>
@endsection
