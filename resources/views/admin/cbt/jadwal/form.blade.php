@extends('layouts.admin')

@section('title', $jadwal->exists ? 'Edit Jadwal CBT' : 'Tambah Jadwal CBT')

@section('content')
    <x-ui-page-header :title="$jadwal->exists ? 'Edit Jadwal CBT' : 'Tambah Jadwal CBT'" description="Atur jalur, jendela waktu, dan komposisi soal jadwal ini.">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.cbt-jadwal.index')" icon="arrow-left">Kembali</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <form method="POST" action="{{ $jadwal->exists ? route('admin.cbt-jadwal.update', $jadwal) : route('admin.cbt-jadwal.store') }}">
        @csrf
        @if ($jadwal->exists) @method('PUT') @endif

        @php
            $komposisiAwal = old('komposisi', $jadwal->exists
                ? $jadwal->komposisi->map(fn ($k) => ['kategori' => $k->kategori, 'jumlah' => $k->jumlah, 'jumlah_prodi' => $k->jumlah_prodi])->values()->all()
                : [['kategori' => '', 'jumlah' => 10, 'jumlah_prodi' => 0]]);
        @endphp

        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6"
             x-data="{ prodiId: @js((string) old('prodi_id', $jadwal->prodi_id ?? '')), rows: @js($komposisiAwal) }"
             x-init="$watch('prodiId', value => { if (!value) rows.forEach(r => r.jumlah_prodi = 0) })">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-ui-label for="jalur_id" required>Jalur</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="jalur_id" id="jalur_id">
                            <option value="">-- Pilih Jalur --</option>
                            @foreach ($jalurList as $j)
                                <option value="{{ $j->id }}" @selected(old('jalur_id', $jadwal->jalur_id) == $j->id)>{{ $j->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                    @error('jalur_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-ui-label for="gelombang_id">Gelombang</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="gelombang_id" id="gelombang_id">
                            <option value="">-- Semua Gelombang --</option>
                            @foreach ($gelombangList as $g)
                                <option value="{{ $g->id }}" @selected(old('gelombang_id', $jadwal->gelombang_id) == $g->id)>{{ $g->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                    @error('gelombang_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <x-ui-label for="prodi_id">Program Studi Target</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="prodi_id" id="prodi_id" x-model="prodiId">
                            <option value="">-- Umum (semua prodi di jalur ini) --</option>
                            @foreach ($prodiList as $p)
                                <option value="{{ $p->id }}" @selected(old('prodi_id', $jadwal->prodi_id) == $p->id)>{{ $p->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">
                        Kosongkan untuk jadwal umum (berlaku semua prodi). Pilih satu prodi (mis. Anestesi) untuk
                        membuat jadwal khusus prodi tsb — baru kuota "Jml. Khusus Prodi" di komposisi bisa diisi.
                    </p>
                    @error('prodi_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <x-ui-label for="nama" required>Nama Jadwal</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="nama" :value="old('nama', $jadwal->nama)" placeholder="Tes CBT Jalur Mandiri Gelombang 1" />
                    </div>
                    @error('nama')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-ui-label for="durasi_menit" required>Durasi (menit)</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" min="1" name="durasi_menit" id="durasi_menit" :value="old('durasi_menit', $jadwal->durasi_menit)" />
                    </div>
                    @error('durasi_menit')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-ui-label for="nilai_kelulusan_minimum">Nilai Kelulusan Minimum</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" step="0.01" min="0" max="100" name="nilai_kelulusan_minimum" id="nilai_kelulusan_minimum" :value="old('nilai_kelulusan_minimum', $jadwal->nilai_kelulusan_minimum)" placeholder="Opsional, skala 0-100" />
                    </div>
                    @error('nilai_kelulusan_minimum')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-ui-label for="waktu_mulai" required>Waktu Mulai</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="datetime-local" name="waktu_mulai" id="waktu_mulai" :value="old('waktu_mulai', $jadwal->waktu_mulai?->format('Y-m-d\TH:i'))" />
                    </div>
                    @error('waktu_mulai')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-ui-label for="waktu_selesai" required>Waktu Selesai</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="datetime-local" name="waktu_selesai" id="waktu_selesai" :value="old('waktu_selesai', $jadwal->waktu_selesai?->format('Y-m-d\TH:i'))" />
                    </div>
                    @error('waktu_selesai')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <p class="mt-4 text-xs text-gray-500">
                Peserta dapat memulai ujian kapan saja dalam rentang waktu mulai-selesai. Batas waktu personal peserta
                dihitung dari saat mereka mulai + durasi (ditambah waktu tambahan aksesibilitas bila ada), dibatasi
                agar tidak melewati waktu selesai jadwal.
            </p>

            <div class="mt-6 border-t border-gray-100 pt-6">
                <h2 class="text-base font-semibold text-gray-900">Komposisi Soal</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Pilih kategori dari bank soal yang sudah ada, lalu tentukan berapa soal umum diambil dari kategori
                    itu. Contoh: Akademik — 4 umum + 4 khusus prodi = 8 soal Akademik untuk jadwal ini. Kolom "Jml.
                    Khusus Prodi" hanya aktif jika Program Studi Target di atas diisi — soalnya diambil dari bank
                    khusus prodi tsb, bukan dari pilihan prodi peserta.
                </p>

                @if ($kategoriList->isEmpty() && ! $jadwal->exists)
                    <div class="mt-4 flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        <x-icon name="warning" class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" />
                        <span>
                            Belum ada kategori di Bank Soal. <a href="{{ route('admin.cbt-soal.create') }}" class="font-semibold underline">Tambah soal</a>
                            dulu dan isi kategorinya, baru kategori itu bisa dipilih di sini.
                        </span>
                    </div>
                @else
                    <div class="mt-4 grid grid-cols-[1fr_7rem_7rem_2.5rem] gap-3 px-1 text-xs font-medium text-gray-500">
                        <span>Kategori</span>
                        <span>Jml. Umum</span>
                        <span>Jml. Khusus Prodi</span>
                        <span></span>
                    </div>

                    <div class="mt-1 space-y-2">
                        <template x-for="(row, index) in rows" :key="index">
                            <div class="grid grid-cols-[1fr_7rem_7rem_2.5rem] items-center gap-3">
                                <select :name="'komposisi[' + index + '][kategori]'" x-model="row.kategori" x-select2
                                        class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach ($kategoriList as $k)
                                        <option value="{{ $k }}">{{ $k }}</option>
                                    @endforeach
                                </select>
                                <input type="number" :name="'komposisi[' + index + '][jumlah]'" x-model.number="row.jumlah" min="1"
                                       class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <input type="number" :name="'komposisi[' + index + '][jumlah_prodi]'" x-model.number="row.jumlah_prodi" min="0"
                                       :disabled="!prodiId" :placeholder="prodiId ? '' : '—'"
                                       class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400">
                                <button type="button" @click="rows.length > 1 && rows.splice(index, 1)"
                                        class="shrink-0 rounded-md p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="rows.push({ kategori: '', jumlah: 10, jumlah_prodi: 0 })"
                            class="mt-3 inline-flex items-center gap-1.5 rounded-lg border border-dashed border-gray-300 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50">
                        <x-icon name="plus" class="h-3.5 w-3.5" /> Tambah Kategori
                    </button>

                    <p class="mt-2 text-xs text-gray-500"
                       x-text="'Total: ' + rows.reduce((sum, r) => sum + (parseInt(r.jumlah) || 0), 0) + ' soal umum, ditambah hingga ' + rows.reduce((sum, r) => sum + (parseInt(r.jumlah_prodi) || 0), 0) + ' soal khusus prodi.'"></p>
                @endif

                @foreach ($errors->keys() as $errorKey)
                    @if (str_starts_with($errorKey, 'komposisi'))
                        <p class="mt-2 text-sm text-red-600">{{ $errors->first($errorKey) }}</p>
                    @endif
                @endforeach
            </div>

            <div class="mt-6">
                <x-ui-toggle name="is_active" id="is_active" :checked="old('is_active', $jadwal->exists ? $jadwal->is_active : true)" label="Aktif" description="Jadwal ini ditampilkan & bisa diakses peserta." />
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.cbt-jadwal.index')">Batal</x-ui-button>
            </div>
        </div>
    </form>
@endsection
