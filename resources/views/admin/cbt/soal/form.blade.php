@extends('layouts.admin')

@section('title', $soal->exists ? 'Edit Soal CBT' : 'Tambah Soal CBT')

@section('content')
    <x-ui-page-header :title="$soal->exists ? 'Edit Soal CBT' : 'Tambah Soal CBT'" description="Isi pertanyaan, pilihan jawaban, dan kunci jawaban.">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.cbt-soal.index')" icon="arrow-left">Kembali</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <form method="POST" action="{{ $soal->exists ? route('admin.cbt-soal.update', $soal) : route('admin.cbt-soal.store') }}">
        @csrf
        @if ($soal->exists) @method('PUT') @endif

        <x-ui-card>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <x-ui-label for="jalur_id">Jalur</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="jalur_id" id="jalur_id">
                            <option value="">-- Umum (lintas jalur) --</option>
                            @foreach ($jalurList as $j)
                                <option value="{{ $j->id }}" @selected(old('jalur_id', $soal->jalur_id) == $j->id)>{{ $j->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                    @error('jalur_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-ui-label for="prodi_id">Program Studi</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="prodi_id" id="prodi_id">
                            <option value="">-- Semua Prodi di Jalur ini --</option>
                            @foreach ($prodiList as $p)
                                <option value="{{ $p->id }}" @selected(old('prodi_id', $soal->prodi_id) == $p->id)>{{ $p->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">Isi bila soal ini khusus untuk satu prodi (mis. Anestesi), berbeda dari soal umum jalur.</p>
                    @error('prodi_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-ui-label for="kategori" required>Kategori</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="kategori" id="kategori" list="kategori-suggest" :value="old('kategori', $soal->kategori)" placeholder="mis. Akademik, Sosial" />
                        <datalist id="kategori-suggest">
                            @foreach ($kategoriList as $k)
                                <option value="{{ $k }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">Menentukan bank soal mana yang dipakai saat komposisi jadwal ujian disusun.</p>
                    @error('kategori')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <x-ui-label for="pertanyaan" required>Pertanyaan</x-ui-label>
                <div class="mt-2">
                    <textarea name="pertanyaan" id="pertanyaan" rows="4"
                        class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>
                </div>
                @error('pertanyaan')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 border-t border-gray-100 pt-6">
                <h2 class="text-base font-semibold text-gray-900">Pilihan Jawaban</h2>
                <p class="mt-1 text-sm text-gray-500">Pilihan A-D wajib diisi, pilihan E opsional. Tandai kunci jawaban di kolom kanan.</p>

                <div class="mt-4 space-y-3">
                    @foreach (['a', 'b', 'c', 'd', 'e'] as $huruf)
                        <div class="flex items-start gap-3">
                            <label class="mt-2.5 flex h-7 w-7 shrink-0 cursor-pointer items-center justify-center rounded-full border border-gray-300 text-xs font-semibold text-gray-500 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-600 has-[:checked]:text-white">
                                <input type="radio" name="kunci_jawaban" value="{{ $huruf }}" class="sr-only"
                                       @checked(old('kunci_jawaban', $soal->kunci_jawaban) === $huruf)>
                                {{ strtoupper($huruf) }}
                            </label>
                            <div class="flex-1">
                                <x-ui-input name="pilihan_{{ $huruf }}" :value="old('pilihan_'.$huruf, $soal->{'pilihan_'.$huruf})" placeholder="Teks pilihan {{ strtoupper($huruf) }}{{ $huruf === 'e' ? ' (opsional)' : '' }}" />
                                @error('pilihan_'.$huruf)
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('kunci_jawaban')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-ui-label for="bobot" required>Bobot Nilai</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" step="0.01" min="0.01" name="bobot" id="bobot" :value="old('bobot', $soal->bobot ?? 1)" />
                    </div>
                    @error('bobot')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-end">
                    <x-ui-toggle name="is_active" id="is_active" :checked="old('is_active', $soal->exists ? $soal->is_active : true)" label="Aktif" description="Soal ikut diacak ke peserta saat ujian dimulai." />
                </div>
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.cbt-soal.index')">Batal</x-ui-button>
            </div>
        </x-ui-card>
    </form>
@endsection
