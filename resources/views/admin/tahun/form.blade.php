@extends('layouts.admin')

@section('title', $tahun->exists ? 'Edit Tahun' : 'Tambah Tahun')

@section('content')
    <x-ui-page-header :title="$tahun->exists ? 'Edit Tahun Penerimaan' : 'Tambah Tahun Penerimaan'">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.tahun.index')" icon="arrow-left">Kembali</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <form method="POST" action="{{ $tahun->exists ? route('admin.tahun.update', $tahun) : route('admin.tahun.store') }}">
        @csrf
        @if ($tahun->exists) @method('PUT') @endif

        <x-ui-card>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-ui-label for="kode" required>Kode</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="kode" id="kode" :value="old('kode', $tahun->kode)" placeholder="2026/2027" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="nama" required>Nama</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="nama" :value="old('nama', $tahun->nama)" placeholder="Tahun Penerimaan 2026/2027" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="status" required>Status</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="status" id="status">
                            @foreach (['draft', 'aktif', 'ditutup', 'arsip'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $tahun->status) === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-ui-label for="tanggal_mulai">Tanggal Mulai</x-ui-label>
                        <div class="mt-2">
                            <x-ui-input type="date" name="tanggal_mulai" id="tanggal_mulai" :value="old('tanggal_mulai', $tahun->tanggal_mulai?->format('Y-m-d'))" />
                        </div>
                    </div>
                    <div>
                        <x-ui-label for="tanggal_selesai">Tanggal Selesai</x-ui-label>
                        <div class="mt-2">
                            <x-ui-input type="date" name="tanggal_selesai" id="tanggal_selesai" :value="old('tanggal_selesai', $tahun->tanggal_selesai?->format('Y-m-d'))" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.tahun.index')">Batal</x-ui-button>
            </div>
        </x-ui-card>
    </form>
@endsection
