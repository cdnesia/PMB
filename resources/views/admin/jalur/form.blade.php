@extends('layouts.admin')

@section('title', $jalur->exists ? 'Edit Jalur' : 'Tambah Jalur')

@section('content')
    <x-ui-page-header :title="$jalur->exists ? 'Edit Jalur' : 'Tambah Jalur'">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.jalur.index')" icon="arrow-left">Kembali</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <form method="POST" action="{{ $jalur->exists ? route('admin.jalur.update', $jalur) : route('admin.jalur.store') }}">
        @csrf
        @if ($jalur->exists) @method('PUT') @endif

        <x-ui-card>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-ui-label for="kode" required>Kode</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="kode" id="kode" :value="old('kode', $jalur->kode)" placeholder="REGULER" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="nama" required>Nama Jalur</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="nama" :value="old('nama', $jalur->nama)" placeholder="Jalur Reguler" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="kategori" required>Kategori</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="kategori" id="kategori">
                            @foreach (['nasional', 'mandiri'] as $k)
                                <option value="{{ $k }}" @selected(old('kategori', $jalur->kategori) === $k)>{{ ucfirst($k) }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="urutan">Urutan Tampil</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" name="urutan" id="urutan" :value="old('urutan', $jalur->urutan)" min="0" />
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <h2 class="text-base font-semibold text-gray-900">Biaya Pendaftaran per Kelas</h2>
                    <p class="mt-1 text-sm text-gray-500">Tentukan biaya pendaftaran untuk tiap kelas perkuliahan di jalur ini. Isi 0 jika gratis.</p>

                    @if ($kelasList->isNotEmpty())
                        <div class="mt-4 overflow-hidden rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3">Kelas Perkuliahan</th>
                                        <th class="w-64 px-4 py-3">Biaya (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($kelasList as $k)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-900">{{ $k->nama }}</td>
                                            <td class="px-4 py-3">
                                                <x-ui-input type="number" name="biaya_kelas[{{ $k->id }}]" id="biaya_kelas_{{ $k->id }}"
                                                    :value="old('biaya_kelas.'.$k->id, $biayaMap[$k->id] ?? 0)" step="0.01" min="0" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="mt-2 text-sm text-gray-400">Belum ada kelas perkuliahan. Tambahkan melalui menu Kelas Perkuliahan.</p>
                    @endif
                </div>
            </div>

            <div class="mt-6 space-y-4">
                <x-ui-toggle name="requires_cbt" id="requires_cbt" :checked="old('requires_cbt', $jalur->requires_cbt)" label="Mewajibkan Tes CBT" description="Pendaftar jalur ini diwajibkan mengikuti tes CBT." />

                <x-ui-toggle name="is_active" id="is_active" :checked="old('is_active', $jalur->is_active)" label="Aktif" description="Jalur dapat dipilih oleh pendaftar." />
            </div>

            {{-- Syarat khusus jalur --}}
            <div class="mt-8 border-t border-gray-100 pt-6" x-data="syaratForm()">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Syarat Khusus Jalur</h2>
                        <p class="mt-1 text-sm text-gray-500">Tambahkan syarat berupa isian (mis. Nomor KIPK) atau unggahan berkas khusus untuk jalur ini.</p>
                    </div>
                    <x-ui-button variant="secondary" type="button" @click="addRow()" icon="plus">Tambah Syarat</x-ui-button>
                </div>

                <div class="mt-4 space-y-3">
                    <template x-for="(row, index) in rows" :key="row.id">
                        <div class="grid grid-cols-1 gap-3 rounded-lg border border-gray-200 p-3 sm:grid-cols-12">
                            <div class="sm:col-span-3">
                                <x-ui-select x-bind:name="'syarat[' + index + '][tipe]'" x-model="row.tipe">
                                    <option value="field">Isian (teks/angka)</option>
                                    <option value="file">Unggah Berkas</option>
                                </x-ui-select>
                            </div>
                            <div class="sm:col-span-4">
                                <input type="text" x-bind:name="'syarat[' + index + '][nama]'" x-model="row.nama"
                                       placeholder="Label, mis. Nomor KIPK"
                                       class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            <div class="sm:col-span-3">
                                <input type="text" x-bind:name="'syarat[' + index + '][kode]'" x-model="row.kode"
                                       placeholder="kode (opsional), mis. nomor_kipk"
                                       class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            <div class="flex items-center gap-3 sm:col-span-2">
                                <label class="flex shrink-0 cursor-pointer select-none items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" x-bind:name="'syarat[' + index + '][wajib]'" value="1" x-model="row.wajib"
                                           class="peer sr-only">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-md border border-gray-300 bg-white text-white transition-colors peer-checked:border-indigo-600 peer-checked:bg-indigo-600">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    </span>
                                    Wajib
                                </label>
                                <button type="button" @click="removeRow(index)" class="shrink-0 rounded-md p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.jalur.index')">Batal</x-ui-button>
            </div>
        </x-ui-card>
    </form>
@endsection

@push('scripts')
<script>
    function syaratForm() {
        return {
            rows: @json($syaratRows),
            nextId: Date.now(),
            init() {
                if (this.rows.length === 0) this.rows = [];
            },
            addRow() {
                this.rows.push({ id: 'new-' + (this.nextId++), tipe: 'field', nama: '', kode: '', wajib: true });
            },
            removeRow(index) {
                this.rows.splice(index, 1);
            },
        };
    }
</script>
@endpush
