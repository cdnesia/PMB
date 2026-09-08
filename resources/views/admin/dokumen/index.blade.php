@extends('layouts.admin')

@section('title', 'Dokumen Persyaratan')

@section('content')
    <div x-data="crudModal({
        storeUrl: @js(route('admin.dokumen.store')),
        defaults: { jalur_id: '', prodi_id: '', rows: [{ nama: '', wajib: false }], is_active: true },
        transform(mode, form) {
            if (mode === 'create') {
                return { jalur_id: form.jalur_id, prodi_id: form.prodi_id, dokumen: form.rows };
            }
            const row = form.rows[0] || { nama: '', wajib: false };
            return { jalur_id: form.jalur_id, prodi_id: form.prodi_id, nama: row.nama, wajib: row.wajib, is_active: form.is_active };
        },
        addRow() { this.form.rows.push({ nama: '', wajib: false }); },
        removeRow(i) { this.form.rows.splice(i, 1); },
    })">
        <x-ui-page-header title="Dokumen Persyaratan" description="Kelola dokumen yang wajib/opsional diunggah per jalur dan prodi.">
            <x-slot:action>
                <x-ui-button variant="primary" type="button" x-on:click="openCreate()" icon="plus">Tambah Dokumen</x-ui-button>
            </x-slot:action>
        </x-ui-page-header>

        <x-ui-card>
            <form method="GET" action="{{ route('admin.dokumen.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <x-ui-label for="jalur_id">Jalur</x-ui-label>
                        <div class="mt-2">
                            <x-ui-select name="jalur_id" id="jalur_id">
                                <option value="">-- Semua Jalur --</option>
                                @foreach ($jalurList as $j)
                                    <option value="{{ $j->id }}" @selected(request('jalur_id') == $j->id)>{{ $j->nama }}</option>
                                @endforeach
                            </x-ui-select>
                        </div>
                    </div>

                    <div>
                        <x-ui-label for="prodi_id">Prodi</x-ui-label>
                        <div class="mt-2">
                            <x-ui-select name="prodi_id" id="prodi_id">
                                <option value="">-- Semua Prodi --</option>
                                @foreach ($prodiList as $p)
                                    <option value="{{ $p->id }}" @selected(request('prodi_id') == $p->id)>{{ $p->jenjang ? $p->jenjang.' - ' : '' }}{{ $p->nama }}</option>
                                @endforeach
                            </x-ui-select>
                        </div>
                    </div>

                    <div>
                        <x-ui-label for="scope">Kategori</x-ui-label>
                        <div class="mt-2">
                            <x-ui-select name="scope" id="scope">
                                <option value="">-- Semua --</option>
                                <option value="jalur" @selected(request('scope') === 'jalur')>Berdasarkan Jalur</option>
                                <option value="prodi" @selected(request('scope') === 'prodi')>Berdasarkan Prodi</option>
                            </x-ui-select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <x-ui-button variant="secondary" type="button" :href="route('admin.dokumen.index')">Reset</x-ui-button>
                    <x-ui-button variant="primary" type="submit">Filter</x-ui-button>
                </div>
            </form>
        </x-ui-card>

        <div id="crud-table" class="mt-6">
            <x-ui-card :padding="''">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Jalur</th>
                                <th class="px-6 py-3">Prodi</th>
                                <th class="px-6 py-3">Nama Dokumen</th>
                                <th class="px-6 py-3">Sifat</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($dokumen as $d)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 text-gray-600">{{ $d->jalur?->nama ?? '-' }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $d->prodi?->jenjang ? $d->prodi->jenjang.' - ' : '' }}{{ $d->prodi?->nama ?? '-' }}</td>
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $d->nama }}</td>
                                    <td class="px-6 py-3">
                                        <x-ui-badge :color="$d->wajib ? 'red' : 'gray'">{{ $d->wajib ? 'Wajib' : 'Opsional' }}</x-ui-badge>
                                    </td>
                                    <td class="px-6 py-3">
                                        <x-ui-badge :color="$d->is_active ? 'green' : 'gray'">{{ $d->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button"
                                                x-on:click="openEdit(@js([
                                                    'id' => $d->id,
                                                    '_updateUrl' => route('admin.dokumen.update', $d),
                                                    'jalur_id' => $d->jalur_id,
                                                    'prodi_id' => $d->prodi_id,
                                                    'rows' => [['nama' => $d->nama, 'wajib' => (bool) $d->wajib]],
                                                    'is_active' => (bool) $d->is_active,
                                                ]))"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </button>
                                            <button type="button"
                                                x-on:click="$dispatch('confirm-delete', { url: @js(route('admin.dokumen.destroy', $d)), message: @js('Hapus dokumen \''.$d->nama.'\'? Tindakan ini tidak bisa dibatalkan.') })"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-ui-empty-state :colspan="6" message="Belum ada dokumen persyaratan." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($dokumen->hasPages())
                    <div class="border-t border-gray-100 px-6 py-3">{{ $dokumen->links() }}</div>
                @endif
            </x-ui-card>
        </div>

        <x-crud-modal create-title="Tambah Dokumen Persyaratan" edit-title="Edit Dokumen Persyaratan" size="xl">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-ui-label for="modal-jalur_id">Jalur</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="jalur_id" id="modal-jalur_id" x-model="form.jalur_id">
                            <option value="">-- Tidak spesifik jalur --</option>
                            @foreach ($jalurList as $j)
                                <option value="{{ $j->id }}">{{ $j->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-prodi_id">Prodi</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="prodi_id" id="modal-prodi_id" x-model="form.prodi_id">
                            <option value="">-- Tidak spesifik prodi --</option>
                            @foreach ($prodiList as $p)
                                <option value="{{ $p->id }}">{{ $p->jenjang ? $p->jenjang.' - ' : '' }}{{ $p->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>
            </div>
            <p class="text-xs text-gray-500">Pilih minimal satu: jalur atau prodi.</p>

            <div class="border-t border-gray-100 pt-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Dokumen</h3>
                    <button type="button" x-show="mode === 'create'" x-on:click="addRow()" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">+ Tambah baris</button>
                </div>

                <div class="mt-3 space-y-3">
                    <template x-for="(row, i) in form.rows" :key="i">
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <x-ui-input placeholder="Nama dokumen, mis. Kartu Keluarga" x-model="row.nama" />
                            </div>
                            <label class="flex shrink-0 cursor-pointer select-none items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" x-model="row.wajib" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                Wajib
                            </label>
                            <button type="button" x-show="mode === 'create' && form.rows.length > 1" x-on:click="removeRow(i)" class="shrink-0 rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600">
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="mode === 'edit'" class="border-t border-gray-100 pt-4">
                <x-ui-toggle name="is_active" id="modal-is_active" x-model="form.is_active" label="Aktif" description="Dokumen ini ditampilkan sebagai persyaratan." />
            </div>
        </x-crud-modal>
    </div>
@endsection
