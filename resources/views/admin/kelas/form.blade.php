@extends('layouts.admin')

@section('title', $kelas->exists ? 'Edit Kelas' : 'Tambah Kelas')

@section('content')
    <x-ui-page-header :title="$kelas->exists ? 'Edit Kelas Perkuliahan' : 'Tambah Kelas Perkuliahan'">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.kelas.index')" icon="arrow-left">Kembali</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <form method="POST" action="{{ $kelas->exists ? route('admin.kelas.update', $kelas) : route('admin.kelas.store') }}">
        @csrf
        @if ($kelas->exists) @method('PUT') @endif

        <x-ui-card>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-ui-label for="kode" required>Kode</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="kode" id="kode" :value="old('kode', $kelas->kode)" placeholder="REGA" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="nama" required>Nama Kelas</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="nama" :value="old('nama', $kelas->nama)" placeholder="Reguler A" />
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <x-ui-toggle name="is_active" id="is_active" :checked="old('is_active', $kelas->is_active)" label="Aktif" description="Kelas dapat dipilih oleh pendaftar." />
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.kelas.index')">Batal</x-ui-button>
            </div>
        </x-ui-card>
    </form>
@endsection
