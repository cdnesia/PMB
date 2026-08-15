@extends('layouts.admin')

@section('title', $promo->exists ? 'Edit Promo' : 'Tambah Promo')

@section('content')
    <x-ui-page-header :title="$promo->exists ? 'Edit Promo' : 'Tambah Promo'">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.promo.index')" icon="arrow-left">Kembali</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <form method="POST" action="{{ $promo->exists ? route('admin.promo.update', $promo) : route('admin.promo.store') }}" x-data="promoKetentuanForm()">
        @csrf
        @if ($promo->exists) @method('PUT') @endif

        <x-ui-card>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-ui-label for="kode" required>Kode Promo</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="kode" id="kode" :value="old('kode', $promo->kode)" placeholder="POTONG50" />
                    </div>
                    @error('kode')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui-label for="nama" required>Nama Promo</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="nama" :value="old('nama', $promo->nama)" placeholder="Potongan Gelombang Awal" />
                    </div>
                    @error('nama')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui-label for="jenis" required>Jenis Biaya yang Dipotong</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="jenis" id="jenis">
                            @foreach (['pendaftaran' => 'Biaya Pendaftaran', 'spp' => 'Biaya SPP', 'semua' => 'Pendaftaran & SPP'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('jenis', $promo->jenis) === $val)>{{ $label }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="tipe" required>Tipe Potongan</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="tipe" id="tipe">
                            <option value="persen" @selected(old('tipe', $promo->tipe) === 'persen')>Persen (%)</option>
                            <option value="nominal" @selected(old('tipe', $promo->tipe) === 'nominal')>Nominal (Rp)</option>
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="nilai" required>Nilai Potongan</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" name="nilai" id="nilai" :value="old('nilai', $promo->nilai)" step="0.01" min="0" placeholder="50" />
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Isi persen (mis. 50) jika tipe persen, atau nominal rupiah jika tipe nominal.</p>
                    @error('nilai')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui-label for="maks_potongan">Maksimal Potongan (Rp)</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" name="maks_potongan" id="maks_potongan" :value="old('maks_potongan', $promo->maks_potongan)" step="0.01" min="0" placeholder="Opsional" />
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Batas atas potongan untuk tipe persen. Kosongkan jika tanpa batas.</p>
                    @error('maks_potongan')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui-label for="tanggal_mulai">Tanggal Mulai</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="date" name="tanggal_mulai" id="tanggal_mulai" :value="old('tanggal_mulai', $promo->tanggal_mulai?->format('Y-m-d'))" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="tanggal_selesai">Tanggal Selesai</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="date" name="tanggal_selesai" id="tanggal_selesai" :value="old('tanggal_selesai', $promo->tanggal_selesai?->format('Y-m-d'))" />
                    </div>
                    @error('tanggal_selesai')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-8 border-t border-gray-100 pt-6">
                <x-ui-toggle name="is_global" id="is_global"
                             x-model="isGlobal"
                             :checked="old('is_global', $promo->is_global)"
                             label="Berlaku Global"
                             description="Promo berlaku untuk semua jalur, prodi, dan kelas (tidak perlu menentukan ketentuan spesifik)." />
            </div>

            <div class="mt-6 border-t border-gray-100 pt-6" x-show="!isGlobal">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Ketentuan Promo (Jalur · Prodi · Kelas)</h2>
                        <p class="mt-1 text-sm text-gray-500">Tentukan kombinasi jalur, prodi, dan kelas perkuliahan yang berhak memakai promo ini. Minimal satu.</p>
                    </div>
                    <x-ui-button variant="secondary" type="button" @click="addRow()" icon="plus">Tambah Ketentuan</x-ui-button>
                </div>

                <div class="mt-4 space-y-3">
                    <template x-for="(row, index) in rows" :key="row.id">
                        <div class="grid grid-cols-1 gap-3 rounded-lg border border-gray-200 p-3 sm:grid-cols-12">
                            <div class="sm:col-span-3">
                                <x-ui-label>Jalur</x-ui-label>
                                <x-ui-select x-bind:name="'ketentuan[' + index + '][jalur_id]'" x-model="row.jalur_id"
                                        @change="row.prodi_id = ''; row.kelas_id = ''" class="mt-1">
                                    <option value="">-- Jalur --</option>
                                    <template x-for="j in jalur" :key="j.id">
                                        <option :value="j.id" x-text="j.nama"></option>
                                    </template>
                                </x-ui-select>
                            </div>
                            <div class="sm:col-span-4">
                                <x-ui-label>Program Studi</x-ui-label>
                                <x-ui-select x-bind:name="'ketentuan[' + index + '][prodi_id]'" x-model="row.prodi_id"
                                        @change="row.kelas_id = ''" class="mt-1">
                                    <option value="">-- Prodi --</option>
                                    <template x-for="p in prodiFor(row.jalur_id)" :key="p.id">
                                        <option :value="p.id" x-text="p.jenjang ? p.jenjang + ' - ' + p.nama : p.nama"></option>
                                    </template>
                                </x-ui-select>
                            </div>
                            <div class="sm:col-span-4">
                                <x-ui-label>Kelas Perkuliahan</x-ui-label>
                                <x-ui-select x-bind:name="'ketentuan[' + index + '][kelas_id]'" x-model="row.kelas_id" class="mt-1">
                                    <option value="">-- Kelas --</option>
                                    <template x-for="k in kelasFor(row.jalur_id, row.prodi_id)" :key="k.id">
                                        <option :value="k.id" x-text="k.nama"></option>
                                    </template>
                                </x-ui-select>
                            </div>
                            <div class="flex items-end justify-end sm:col-span-1">
                                <button type="button" @click="removeRow(index)" class="rounded-md p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </template>

                    <template x-if="rows.length === 0">
                        <p class="text-sm text-gray-400">Belum ada ketentuan. Klik "Tambah Ketentuan".</p>
                    </template>
                </div>
                @error('ketentuan')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <x-ui-toggle name="is_active" id="is_active" :checked="old('is_active', $promo->exists ? $promo->is_active : true)" label="Aktif" description="Promo dapat dipilih oleh pendaftar." />
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.promo.index')">Batal</x-ui-button>
            </div>
        </x-ui-card>
    </form>
@endsection

@push('scripts')
<script>
    function promoKetentuanForm() {
        return {
            isGlobal: @json((bool) ($promo->is_global ?? false)),
            rows: @json($ketentuanRows),
            jalur: @json($jalurList->map(fn ($j) => ['id' => $j->id, 'nama' => $j->nama])->values()),
            matriks: @json($matriksMap),
            nextId: Date.now(),
            addRow() {
                this.rows.push({ id: 'new-' + (this.nextId++), jalur_id: '', prodi_id: '', kelas_id: '' });
            },
            removeRow(index) {
                this.rows.splice(index, 1);
            },
            prodiFor(jalurId) {
                if (!jalurId) return [];
                const m = this.matriks[jalurId] || {};
                return Object.keys(m).map(id => ({ id, nama: m[id].nama, jenjang: m[id].jenjang }));
            },
            kelasFor(jalurId, prodiId) {
                if (!jalurId || !prodiId) return [];
                const m = this.matriks[jalurId] || {};
                return (m[prodiId] && m[prodiId].kelas) || [];
            },
        };
    }
</script>
@endpush
