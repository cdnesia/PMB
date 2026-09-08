@extends('layouts.admin')

@section('title', 'Promo')

@section('content')
    <div x-data="crudModal({
        storeUrl: @js(route('admin.promo.store')),
        defaults: {
            kode: '', nama: '', jenis: 'pendaftaran', tipe: 'persen', nilai: '', maks_potongan: '',
            tanggal_mulai: '', tanggal_selesai: '', is_global: false, is_active: true, ketentuan: [],
        },
        jalur: @js($jalurList->map(fn ($j) => ['id' => $j->id, 'nama' => $j->nama])->values()),
        matriks: @js($matriksMap),
        addKetentuan() { this.form.ketentuan.push({ jalur_id: '', prodi_id: '', kelas_id: '' }); },
        removeKetentuan(i) { this.form.ketentuan.splice(i, 1); },
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
    })">
        <x-ui-page-header title="Promo" description="Kelola promo potongan biaya pendaftaran dan/atau SPP.">
            <x-slot:action>
                <x-ui-button variant="primary" type="button" x-on:click="openCreate()" icon="plus">Tambah Promo</x-ui-button>
            </x-slot:action>
        </x-ui-page-header>

        <div id="crud-table">
            <x-ui-card :padding="''">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Kode</th>
                                <th class="px-6 py-3">Nama</th>
                                <th class="px-6 py-3">Jenis</th>
                                <th class="px-6 py-3 text-right">Potongan</th>
                                <th class="px-6 py-3">Ketentuan (Jalur · Prodi · Kelas)</th>
                                <th class="px-6 py-3">Periode Berlaku</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($promo as $p)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $p->kode }}</td>
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $p->nama }}</td>
                                    <td class="px-6 py-3">
                                        <x-ui-badge :color="match ($p->jenis) { 'spp' => 'amber', 'semua' => 'indigo', default => 'blue' }">
                                            {{ $p->labelJenis() }}
                                        </x-ui-badge>
                                    </td>
                                    <td class="px-6 py-3 text-right font-medium text-gray-900">{{ $p->labelPotongan() }}</td>
                                    <td class="px-6 py-3 text-gray-600">
                                        @if ($p->is_global)
                                            <x-ui-badge color="green">Global — semua kombinasi</x-ui-badge>
                                        @elseif ($p->ketentuan->isNotEmpty())
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($p->ketentuan as $k)
                                                    <span class="rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700">
                                                        {{ $k->jalur?->nama ?? '—' }} · {{ $k->prodi?->jenjang ? $k->prodi->jenjang.' - ' : '' }}{{ $k->prodi?->nama ?? '—' }} · {{ $k->kelas?->nama ?? '—' }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-gray-600">
                                        @if ($p->tanggal_mulai || $p->tanggal_selesai)
                                            {{ $p->tanggal_mulai?->format('d/m/Y') ?? '—' }} s/d {{ $p->tanggal_selesai?->format('d/m/Y') ?? '—' }}
                                        @else
                                            <span class="text-xs text-gray-400">Tanpa batas</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3">
                                        <x-ui-badge :color="$p->isBerlaku() ? 'green' : 'gray'">{{ $p->isBerlaku() ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button"
                                                x-on:click="openEdit(@js([
                                                    'id' => $p->id,
                                                    '_updateUrl' => route('admin.promo.update', $p),
                                                    'kode' => $p->kode,
                                                    'nama' => $p->nama,
                                                    'jenis' => $p->jenis,
                                                    'tipe' => $p->tipe,
                                                    'nilai' => $p->nilai,
                                                    'maks_potongan' => $p->maks_potongan,
                                                    'tanggal_mulai' => $p->tanggal_mulai?->format('Y-m-d'),
                                                    'tanggal_selesai' => $p->tanggal_selesai?->format('Y-m-d'),
                                                    'is_global' => (bool) $p->is_global,
                                                    'is_active' => (bool) $p->is_active,
                                                    'ketentuan' => $p->ketentuan->map(fn ($k) => ['jalur_id' => $k->jalur_id, 'prodi_id' => $k->prodi_id, 'kelas_id' => $k->kelas_id])->values(),
                                                ]))"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </button>
                                            <button type="button"
                                                x-on:click="$dispatch('confirm-delete', { url: @js(route('admin.promo.destroy', $p)), message: @js('Hapus promo \''.$p->nama.'\'? Tindakan ini tidak bisa dibatalkan.') })"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-ui-empty-state :colspan="8" message="Belum ada promo." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($promo->hasPages())
                    <div class="border-t border-gray-100 px-6 py-3">{{ $promo->links() }}</div>
                @endif
            </x-ui-card>
        </div>

        <x-crud-modal create-title="Tambah Promo" edit-title="Edit Promo" size="3xl">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-ui-label for="modal-kode" required>Kode Promo</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="kode" id="modal-kode" x-model="form.kode" placeholder="POTONG50" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-nama" required>Nama Promo</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="modal-nama" x-model="form.nama" placeholder="Potongan Gelombang Awal" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-jenis" required>Jenis Biaya yang Dipotong</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="jenis" id="modal-jenis" x-model="form.jenis" :searchable="false">
                            @foreach (['pendaftaran' => 'Biaya Pendaftaran', 'spp' => 'Biaya SPP', 'semua' => 'Pendaftaran & SPP'] as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-tipe" required>Tipe Potongan</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="tipe" id="modal-tipe" x-model="form.tipe" :searchable="false">
                            <option value="persen">Persen (%)</option>
                            <option value="nominal">Nominal (Rp)</option>
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-nilai" required>Nilai Potongan</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" name="nilai" id="modal-nilai" x-model.number="form.nilai" step="0.01" min="0" placeholder="50" />
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Isi persen (mis. 50) jika tipe persen, atau nominal rupiah jika tipe nominal.</p>
                </div>

                <div>
                    <x-ui-label for="modal-maks_potongan">Maksimal Potongan (Rp)</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" name="maks_potongan" id="modal-maks_potongan" x-model="form.maks_potongan" step="0.01" min="0" placeholder="Opsional" />
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Batas atas potongan untuk tipe persen. Kosongkan jika tanpa batas.</p>
                </div>

                <div>
                    <x-ui-label for="modal-tanggal_mulai">Tanggal Mulai</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="date" name="tanggal_mulai" id="modal-tanggal_mulai" x-model="form.tanggal_mulai" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-tanggal_selesai">Tanggal Selesai</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="date" name="tanggal_selesai" id="modal-tanggal_selesai" x-model="form.tanggal_selesai" />
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <x-ui-toggle name="is_global" id="modal-is_global" x-model="form.is_global" label="Berlaku Global" description="Promo berlaku untuk semua jalur, prodi, dan kelas (tidak perlu menentukan ketentuan spesifik)." />
            </div>

            <div class="border-t border-gray-100 pt-4" x-show="!form.is_global">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Ketentuan Promo (Jalur · Prodi · Kelas)</h3>
                        <p class="mt-1 text-xs text-gray-500">Kombinasi jalur, prodi, dan kelas yang berhak memakai promo ini. Minimal satu.</p>
                    </div>
                    <button type="button" x-on:click="addKetentuan()" class="shrink-0 text-xs font-medium text-indigo-600 hover:text-indigo-700">+ Tambah Ketentuan</button>
                </div>

                <div class="mt-3 space-y-3">
                    <template x-for="(row, index) in form.ketentuan" :key="index">
                        <div class="grid grid-cols-1 gap-3 rounded-lg border border-gray-200 p-3 sm:grid-cols-12">
                            <div class="sm:col-span-3">
                                <x-ui-label>Jalur</x-ui-label>
                                <select x-model="row.jalur_id" x-on:change="row.prodi_id = ''; row.kelas_id = ''"
                                        class="mt-1 block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="">-- Jalur --</option>
                                    <template x-for="j in jalur" :key="j.id">
                                        <option :value="j.id" x-text="j.nama"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="sm:col-span-4">
                                <x-ui-label>Program Studi</x-ui-label>
                                <select x-model="row.prodi_id" x-on:change="row.kelas_id = ''"
                                        class="mt-1 block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="">-- Prodi --</option>
                                    <template x-for="p in prodiFor(row.jalur_id)" :key="p.id">
                                        <option :value="p.id" x-text="p.jenjang ? p.jenjang + ' - ' + p.nama : p.nama"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="sm:col-span-4">
                                <x-ui-label>Kelas Perkuliahan</x-ui-label>
                                <select x-model="row.kelas_id"
                                        class="mt-1 block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="">-- Kelas --</option>
                                    <template x-for="k in kelasFor(row.jalur_id, row.prodi_id)" :key="k.id">
                                        <option :value="k.id" x-text="k.nama"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="flex items-end justify-end sm:col-span-1">
                                <button type="button" x-on:click="removeKetentuan(index)" class="rounded-md p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </template>

                    <template x-if="form.ketentuan.length === 0">
                        <p class="text-sm text-gray-400">Belum ada ketentuan. Klik "Tambah Ketentuan".</p>
                    </template>
                </div>
            </div>

            <x-ui-toggle name="is_active" id="modal-is_active" x-model="form.is_active" label="Aktif" description="Promo dapat dipilih oleh pendaftar." />
        </x-crud-modal>
    </div>
@endsection
