@extends('layouts.admin')

@section('title', 'Edit Dokumen Persyaratan')

@section('content')
    <x-ui-page-header title="Edit Dokumen Persyaratan">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.dokumen.index')" icon="arrow-left">Kembali</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <form method="POST" action="{{ route('admin.dokumen.update', $dokumen) }}">
        @csrf
        @method('PUT')

        <x-ui-card>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-ui-label for="jalur_id">Jalur (opsional)</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="jalur_id" id="jalur_id">
                            <option value="">-- Berlaku untuk semua jalur --</option>
                            @foreach ($jalurList as $j)
                                <option value="{{ $j->id }}" @selected(old('jalur_id', $dokumen->jalur_id) == $j->id)>{{ $j->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="prodi_id">Prodi (opsional)</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="prodi_id" id="prodi_id">
                            <option value="">-- Berlaku untuk semua prodi --</option>
                            @foreach ($prodiList as $p)
                                <option value="{{ $p->id }}" @selected(old('prodi_id', $dokumen->prodi_id) == $p->id)>{{ $p->jenjang ? $p->jenjang.' - ' : '' }}{{ $p->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <x-ui-label for="nama" required>Nama Dokumen</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="nama" :value="old('nama', $dokumen->nama)" placeholder="Contoh: Ijazah / Rapor" />
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                <x-ui-toggle name="wajib" id="wajib" :checked="old('wajib', $dokumen->wajib)" label="Wajib diunggah" description="Pendaftar harus mengunggah dokumen ini." />

                <x-ui-toggle name="is_active" id="is_active" :checked="old('is_active', $dokumen->is_active)" label="Aktif" description="Dokumen berlaku sebagai persyaratan." />
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.dokumen.index')">Batal</x-ui-button>
            </div>
        </x-ui-card>
    </form>
@endsection
