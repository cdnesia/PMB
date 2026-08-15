@extends('layouts.admin')

@section('title', $kuota->exists ? 'Edit Kuota' : 'Tambah Kuota')

@section('content')
    <x-ui-page-header :title="$kuota->exists ? 'Edit Kuota' : 'Tambah Kuota'">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.kuota.index')" icon="arrow-left">Kembali</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <form method="POST" action="{{ $kuota->exists ? route('admin.kuota.update', $kuota) : route('admin.kuota.store') }}">
        @csrf
        @if ($kuota->exists) @method('PUT') @endif

        <x-ui-card>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-ui-label for="tahun_id" required>Tahun Penerimaan</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="tahun_id" id="tahun_id">
                            <option value="">-- Pilih Tahun --</option>
                            @foreach ($tahunList as $t)
                                <option value="{{ $t->id }}" @selected(old('tahun_id', $kuota->tahun_id) == $t->id)>{{ $t->kode }} ({{ $t->status }})</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="jalur_id" required>Jalur</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="jalur_id" id="jalur_id">
                            <option value="">-- Pilih Jalur --</option>
                            @foreach ($jalurList as $j)
                                <option value="{{ $j->id }}" @selected(old('jalur_id', $kuota->jalur_id) == $j->id)>{{ $j->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="prodi_id" required>Program Studi</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="prodi_id" id="prodi_id">
                            <option value="">-- Pilih Prodi --</option>
                            @foreach ($prodiList as $p)
                                <option value="{{ $p->id }}" @selected(old('prodi_id', $kuota->prodi_id) == $p->id)>{{ $p->jenjang ? $p->jenjang.' - ' : '' }}{{ $p->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="kelas_id">Kelas Perkuliahan (opsional)</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="kelas_id" id="kelas_id">
                            <option value="">-- Semua Kelas --</option>
                            @foreach ($kelasList as $k)
                                <option value="{{ $k->id }}" @selected(old('kelas_id', $kuota->kelas_id) == $k->id)>{{ $k->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="jumlah" required>Jumlah Kuota</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" name="jumlah" id="jumlah" :value="old('jumlah', $kuota->jumlah)" min="0" />
                    </div>
                    @if ($kuota->exists && $kuota->terpakai > 0)
                        <p class="mt-1 text-xs text-amber-600">Sudah terpakai {{ $kuota->terpakai }} kursi — jumlah tidak boleh kurang dari ini.</p>
                    @endif
                </div>
            </div>

            <div class="mt-6">
                <x-ui-toggle name="is_active" id="is_active" :checked="old('is_active', $kuota->is_active)" label="Buka" description="Kuota aktif dan dapat diisi pendaftar." />
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.kuota.index')">Batal</x-ui-button>
            </div>
        </x-ui-card>
    </form>
@endsection
