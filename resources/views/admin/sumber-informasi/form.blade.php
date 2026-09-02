@extends('layouts.admin')

@section('title', $sumber->exists ? 'Edit Sumber Informasi' : 'Tambah Sumber Informasi')

@section('content')
    <x-ui-page-header :title="$sumber->exists ? 'Edit Sumber Informasi' : 'Tambah Sumber Informasi'">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.sumber-informasi.index')" icon="arrow-left">Kembali</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <form method="POST" action="{{ $sumber->exists ? route('admin.sumber-informasi.update', $sumber) : route('admin.sumber-informasi.store') }}">
        @csrf
        @if ($sumber->exists) @method('PUT') @endif

        <x-ui-card>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-ui-label for="kode" required>Kode</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="kode" id="kode" :value="old('kode', $sumber->kode)" placeholder="MEDIA-SOSIAL" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="nama" required>Nama</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="nama" :value="old('nama', $sumber->nama)" placeholder="Media Sosial" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="urutan">Urutan Tampil</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" name="urutan" id="urutan" :value="old('urutan', $sumber->urutan ?? 0)" min="0" />
                    </div>
                    <p class="mt-1.5 text-xs text-gray-400">Angka lebih kecil tampil lebih dulu di pilihan registrasi.</p>
                </div>
            </div>

            <div class="mt-6">
                <x-ui-toggle name="is_active" id="is_active" :checked="old('is_active', $sumber->exists ? $sumber->is_active : true)" label="Aktif" description="Tampil sebagai pilihan di form registrasi akun." />
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.sumber-informasi.index')">Batal</x-ui-button>
            </div>
        </x-ui-card>
    </form>
@endsection
