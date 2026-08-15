@extends('layouts.admin')

@section('title', $prodi->exists ? 'Edit Prodi' : 'Tambah Prodi')

@section('content')
    <x-ui-page-header :title="$prodi->exists ? 'Edit Prodi' : 'Tambah Prodi'">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.prodi.index')" icon="arrow-left">Kembali</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <form method="POST" action="{{ $prodi->exists ? route('admin.prodi.update', $prodi) : route('admin.prodi.store') }}">
        @csrf
        @if ($prodi->exists) @method('PUT') @endif

        <x-ui-card>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-ui-label for="kode" required>Kode</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="kode" id="kode" :value="old('kode', $prodi->kode)" placeholder="TI" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="nama" required>Nama Program Studi</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="nama" :value="old('nama', $prodi->nama)" placeholder="Teknik Informatika" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="jenjang" required>Jenjang</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="jenjang" id="jenjang">
                            @foreach (['D3', 'D4', 'S1', 'S2'] as $j)
                                <option value="{{ $j }}" @selected(old('jenjang', $prodi->jenjang) === $j)>{{ $j }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="fakultas">Fakultas</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="fakultas" id="fakultas" :value="old('fakultas', $prodi->fakultas)" placeholder="Teknik" />
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <x-ui-toggle name="is_active" id="is_active" :checked="old('is_active', $prodi->is_active)" label="Aktif" description="Prodi dapat dipilih oleh pendaftar." />
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
                <x-ui-button variant="secondary" :href="route('admin.prodi.index')">Batal</x-ui-button>
            </div>
        </x-ui-card>
    </form>
@endsection
