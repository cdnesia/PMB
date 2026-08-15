@extends('layouts.admin')

@section('title', $gelombang->exists ? 'Edit Gelombang' : 'Tambah Gelombang')

@section('content')
    <x-ui-page-header :title="$gelombang->exists ? 'Edit Gelombang' : 'Tambah Gelombang'" description="Atur periode pendaftaran dan jalur yang tersedia di gelombang ini.">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.gelombang.index')" icon="arrow-left">Kembali</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <form method="POST" action="{{ $gelombang->exists ? route('admin.gelombang.update', $gelombang) : route('admin.gelombang.store') }}">
        @csrf
        @if ($gelombang->exists) @method('PUT') @endif

        <x-ui-card>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-ui-label for="tahun_id" required>Tahun Penerimaan</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="tahun_id" id="tahun_id">
                            <option value="">-- Pilih Tahun --</option>
                            @foreach ($tahunList as $t)
                                <option value="{{ $t->id }}" @selected(old('tahun_id', $gelombang->tahun_id) == $t->id)>{{ $t->kode }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                    @error('tahun_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-ui-label for="nama" required>Nama Gelombang</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="nama" :value="old('nama', $gelombang->nama)" placeholder="Gelombang 1" />
                    </div>
                    @error('nama')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-ui-label for="tanggal_mulai" required>Tanggal Mulai Pendaftaran</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="date" name="tanggal_mulai" id="tanggal_mulai" :value="old('tanggal_mulai', $gelombang->tanggal_mulai?->format('Y-m-d'))" />
                    </div>
                    @error('tanggal_mulai')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-ui-label for="tanggal_selesai" required>Tanggal Selesai Pendaftaran</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="date" name="tanggal_selesai" id="tanggal_selesai" :value="old('tanggal_selesai', $gelombang->tanggal_selesai?->format('Y-m-d'))" />
                    </div>
                    @error('tanggal_selesai')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-ui-label for="tanggal_pengumuman">Tanggal Pengumuman</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="date" name="tanggal_pengumuman" id="tanggal_pengumuman" :value="old('tanggal_pengumuman', $gelombang->tanggal_pengumuman?->format('Y-m-d'))" />
                    </div>
                    @error('tanggal_pengumuman')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 border-t border-gray-100 pt-6">
                <h2 class="text-base font-semibold text-gray-900">Jalur yang Tersedia</h2>
                <p class="mt-1 text-sm text-gray-500">Centang jalur yang berlaku di gelombang ini. Minimal satu jalur.</p>

                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($jalurList as $j)
                        <label class="flex cursor-pointer select-none items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 transition hover:bg-gray-50 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50">
                            <input type="checkbox"
                                   name="jalur[]"
                                   value="{{ $j->id }}"
                                   @checked(in_array($j->id, old('jalur', $selectedJalur->all())))
                                   class="peer sr-only">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-white transition-colors peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-600 peer-focus-visible:ring-offset-2">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                            <span class="text-sm font-medium text-gray-700">{{ $j->nama }}</span>
                        </label>
                    @endforeach
                </div>
                @error('jalur')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <x-ui-toggle name="is_active" id="is_active" :checked="old('is_active', $gelombang->is_active)" label="Aktif" description="Gelombang sedang dibuka untuk pendaftaran." />
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.gelombang.index')">Batal</x-ui-button>
            </div>
        </x-ui-card>
    </form>
@endsection
