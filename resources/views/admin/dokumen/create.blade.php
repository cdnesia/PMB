@extends('layouts.admin')

@section('title', 'Tambah Dokumen Persyaratan')

@section('content')
    <x-ui-page-header title="Tambah Dokumen Persyaratan" description="Tambahkan satu atau beberapa dokumen sekaligus untuk jalur/prodi tertentu.">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.dokumen.index')" icon="arrow-left">Kembali</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <form method="POST" action="{{ route('admin.dokumen.store') }}" x-data="dokumenForm()">
        @csrf

        <x-ui-card>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-ui-label for="jalur_id">Jalur (opsional)</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="jalur_id" id="jalur_id" x-model="jalurId" @change="prodiId = ''">
                            <option value="">-- Berlaku untuk semua jalur --</option>
                            @foreach ($jalurList as $j)
                                <option value="{{ $j->id }}" @selected(old('jalur_id') == $j->id)>{{ $j->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="prodi_id">Prodi (opsional)</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="prodi_id" id="prodi_id" x-model="prodiId" @change="jalurId = ''">
                            <option value="">-- Berlaku untuk semua prodi --</option>
                            @foreach ($prodiList as $p)
                                <option value="{{ $p->id }}" @selected(old('prodi_id') == $p->id)>{{ $p->jenjang ? $p->jenjang.' - ' : '' }}{{ $p->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-400">Jika keduanya dikosongkan, dokumen berlaku umum.</p>
                </div>
            </div>

            <div class="mt-8 border-t border-gray-100 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Daftar Dokumen</h2>
                        <p class="mt-1 text-sm text-gray-500">Masukkan nama dokumen secara dinamis. Tandai "Wajib" bila harus diunggah.</p>
                    </div>
                    <x-ui-button variant="secondary" type="button" @click="addRow()" icon="plus">Tambah Baris</x-ui-button>
                </div>

                <div class="mt-4 space-y-3">
                    <template x-for="(row, index) in rows" :key="row.id">
                        <div class="flex items-start gap-3 rounded-lg border border-gray-200 p-3">
                            <div class="flex-1">
                                <input type="text"
                                       :name="'dokumen[' + index + '][nama]'"
                                       x-model="row.nama"
                                       placeholder="Contoh: Ijazah / Rapor"
                                       class="block w-full rounded-lg border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>

                            <label class="flex shrink-0 cursor-pointer select-none items-center gap-2 pt-2 text-sm text-gray-700">
                                <input type="checkbox"
                                       :name="'dokumen[' + index + '][wajib]'"
                                       value="1"
                                       x-model="row.wajib"
                                       class="peer sr-only">
                                <span class="flex h-5 w-5 items-center justify-center rounded-md border border-gray-300 bg-white text-white transition-colors peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-600 peer-focus-visible:ring-offset-2">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </span>
                                Wajib
                            </label>

                            <button type="button" @click="removeRow(index)"
                                    class="shrink-0 rounded-md p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600">
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.dokumen.index')">Batal</x-ui-button>
            </div>
        </x-ui-card>
    </form>
@endsection

@push('scripts')
<script>
    function dokumenForm() {
        return {
            jalurId: null,
            prodiId: null,
            rows: [{ id: Date.now(), nama: '', wajib: true }],
            addRow() {
                this.rows.push({ id: Date.now(), nama: '', wajib: true });
            },
            removeRow(index) {
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                }
            },
        };
    }
</script>
@endpush
