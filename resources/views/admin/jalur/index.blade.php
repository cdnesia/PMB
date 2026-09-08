@extends('layouts.admin')

@section('title', 'Jalur & Biaya')

@section('content')
    <div x-data="crudModal({
        storeUrl: @js(route('admin.jalur.store')),
        defaults: {
            kode: '', nama: '', kategori: 'nasional', urutan: 0,
            biaya_kelas: @js($kelasList->mapWithKeys(fn ($k) => [$k->id => 0])),
            requires_cbt: false, is_active: true, syarat: [],
        },
        addSyarat() { this.form.syarat.push({ tipe: 'field', nama: '', kode: '', wajib: true }); },
        removeSyarat(i) { this.form.syarat.splice(i, 1); },
    })">
        <x-ui-page-header title="Jalur & Biaya" description="Kelola jalur penerimaan beserta biaya pendaftarannya.">
            <x-slot:action>
                <x-ui-button variant="primary" type="button" x-on:click="openCreate()" icon="plus">Tambah Jalur</x-ui-button>
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
                                <th class="px-6 py-3">Kategori</th>
                                <th class="px-6 py-3">Urutan</th>
                                <th class="px-6 py-3">Biaya per Kelas</th>
                                <th class="px-6 py-3">Tes CBT</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($jalur as $j)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $j->kode }}</td>
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $j->nama }}</td>
                                    <td class="px-6 py-3">
                                        <x-ui-badge :color="$j->kategori === 'nasional' ? 'blue' : 'indigo'">{{ ucfirst($j->kategori) }}</x-ui-badge>
                                    </td>
                                    <td class="px-6 py-3 text-gray-600">{{ $j->urutan }}</td>
                                    <td class="px-6 py-3">
                                        @if ($j->kelasBiaya->isNotEmpty())
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($j->kelasBiaya->sortBy(fn ($b) => $b->kelas?->nama) as $b)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                                        {{ $b->kelas?->nama }}: Rp {{ number_format($b->biaya_pendaftaran, 0, ',', '.') }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3">
                                        @if ($j->requires_cbt)
                                            <x-ui-badge color="amber">CBT</x-ui-badge>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3">
                                        <x-ui-badge :color="$j->is_active ? 'green' : 'gray'">{{ $j->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button"
                                                x-on:click="openEdit(@js([
                                                    'id' => $j->id,
                                                    '_updateUrl' => route('admin.jalur.update', $j),
                                                    'kode' => $j->kode,
                                                    'nama' => $j->nama,
                                                    'kategori' => $j->kategori,
                                                    'urutan' => $j->urutan,
                                                    'biaya_kelas' => $kelasList->mapWithKeys(fn ($k) => [$k->id => (float) ($j->kelasBiaya->firstWhere('kelas_id', $k->id)?->biaya_pendaftaran ?? 0)]),
                                                    'requires_cbt' => (bool) $j->requires_cbt,
                                                    'is_active' => (bool) $j->is_active,
                                                    'syarat' => $j->syarat->map(fn ($s) => ['tipe' => $s->tipe, 'nama' => $s->nama, 'kode' => $s->kode, 'wajib' => (bool) $s->wajib])->values(),
                                                ]))"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </button>
                                            <button type="button"
                                                x-on:click="$dispatch('confirm-delete', { url: @js(route('admin.jalur.destroy', $j)), message: @js('Hapus jalur \''.$j->nama.'\'? Tindakan ini tidak bisa dibatalkan.') })"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-ui-empty-state :colspan="8" message="Belum ada jalur." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($jalur->hasPages())
                    <div class="border-t border-gray-100 px-6 py-3">{{ $jalur->links() }}</div>
                @endif
            </x-ui-card>
        </div>

        <x-crud-modal create-title="Tambah Jalur" edit-title="Edit Jalur" size="3xl">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-ui-label for="modal-kode" required>Kode</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="kode" id="modal-kode" x-model="form.kode" placeholder="REGULER" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-nama" required>Nama Jalur</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="modal-nama" x-model="form.nama" placeholder="Jalur Reguler" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-kategori" required>Kategori</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="kategori" id="modal-kategori" x-model="form.kategori" :searchable="false">
                            @foreach (['nasional', 'mandiri'] as $k)
                                <option value="{{ $k }}">{{ ucfirst($k) }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-urutan">Urutan Tampil</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" name="urutan" id="modal-urutan" x-model.number="form.urutan" min="0" />
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <h3 class="text-sm font-semibold text-gray-900">Biaya Pendaftaran per Kelas</h3>
                <p class="mt-1 text-xs text-gray-500">Isi 0 jika gratis.</p>

                @if ($kelasList->isNotEmpty())
                    <div class="mt-3 space-y-2">
                        @foreach ($kelasList as $k)
                            <div class="flex items-center gap-3">
                                <span class="w-1/2 text-sm text-gray-700">{{ $k->nama }}</span>
                                <div class="flex-1">
                                    <x-ui-input type="number" step="0.01" min="0" x-model.number="form.biaya_kelas['{{ $k->id }}']" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-2 text-sm text-gray-400">Belum ada kelas perkuliahan.</p>
                @endif
            </div>

            <div class="space-y-4 border-t border-gray-100 pt-4">
                <x-ui-toggle name="requires_cbt" id="modal-requires_cbt" x-model="form.requires_cbt" label="Mewajibkan Tes CBT" description="Pendaftar jalur ini diwajibkan mengikuti tes CBT." />
                <x-ui-toggle name="is_active" id="modal-is_active" x-model="form.is_active" label="Aktif" description="Jalur dapat dipilih oleh pendaftar." />
            </div>

            <div class="border-t border-gray-100 pt-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Syarat Khusus Jalur</h3>
                        <p class="mt-1 text-xs text-gray-500">Isian (mis. Nomor KIPK) atau unggahan berkas khusus untuk jalur ini.</p>
                    </div>
                    <button type="button" x-on:click="addSyarat()" class="shrink-0 text-xs font-medium text-indigo-600 hover:text-indigo-700">+ Tambah Syarat</button>
                </div>

                <div class="mt-3 space-y-3">
                    <template x-for="(row, index) in form.syarat" :key="index">
                        <div class="grid grid-cols-1 gap-3 rounded-lg border border-gray-200 p-3 sm:grid-cols-12">
                            <div class="sm:col-span-3">
                                <select x-model="row.tipe"
                                        class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="field">Isian (teks/angka)</option>
                                    <option value="file">Unggah Berkas</option>
                                </select>
                            </div>
                            <div class="sm:col-span-4">
                                <input type="text" x-model="row.nama" placeholder="Label, mis. Nomor KIPK"
                                       class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            <div class="sm:col-span-3">
                                <input type="text" x-model="row.kode" placeholder="kode (opsional)"
                                       class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            <div class="flex items-center gap-3 sm:col-span-2">
                                <label class="flex shrink-0 cursor-pointer select-none items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" x-model="row.wajib" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    Wajib
                                </label>
                                <button type="button" x-on:click="removeSyarat(index)" class="shrink-0 rounded-md p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </x-crud-modal>
    </div>
@endsection
