@extends('layouts.admin')

@section('title', 'Kuota Prodi')

@section('content')
    <div x-data="crudModal({
        storeUrl: @js(route('admin.kuota.store')),
        defaults: { tahun_id: '', jalur_id: '', prodi_id: '', kelas_id: '', jumlah: 0, is_active: true, terpakai: 0 },
    })">
        <x-ui-page-header title="Kuota Prodi" description="Atur jumlah kuota per tahun, jalur, prodi, dan kelas.">
            <x-slot:action>
                <x-ui-button variant="primary" type="button" x-on:click="openCreate()" icon="plus">Tambah Kuota</x-ui-button>
            </x-slot:action>
        </x-ui-page-header>

        <div id="crud-table">
            <x-ui-card :padding="''">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Tahun</th>
                                <th class="px-6 py-3">Jalur</th>
                                <th class="px-6 py-3">Prodi</th>
                                <th class="px-6 py-3">Kelas</th>
                                <th class="px-6 py-3 text-right">Jumlah</th>
                                <th class="px-6 py-3 text-right">Terpakai</th>
                                <th class="px-6 py-3 text-right">Sisa</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($kuota as $k)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $k->tahun?->kode }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $k->jalur?->nama }}</td>
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $k->prodi?->jenjang ? $k->prodi->jenjang.' - ' : '' }}{{ $k->prodi?->nama }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $k->kelas?->nama ?? 'Semua kelas' }}</td>
                                    <td class="px-6 py-3 text-right text-gray-900">{{ $k->jumlah }}</td>
                                    <td class="px-6 py-3 text-right text-gray-600">{{ $k->terpakai }}</td>
                                    <td class="px-6 py-3 text-right font-medium {{ $k->sisa === 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $k->sisa }}</td>
                                    <td class="px-6 py-3">
                                        <x-ui-badge :color="$k->is_active ? 'green' : 'gray'">{{ $k->is_active ? 'Buka' : 'Tutup' }}</x-ui-badge>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button"
                                                x-on:click="openEdit(@js([
                                                    'id' => $k->id,
                                                    '_updateUrl' => route('admin.kuota.update', $k),
                                                    'tahun_id' => $k->tahun_id,
                                                    'jalur_id' => $k->jalur_id,
                                                    'prodi_id' => $k->prodi_id,
                                                    'kelas_id' => $k->kelas_id,
                                                    'jumlah' => $k->jumlah,
                                                    'terpakai' => $k->terpakai,
                                                    'is_active' => (bool) $k->is_active,
                                                ]))"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </button>
                                            <button type="button"
                                                x-on:click="$dispatch('confirm-delete', { url: @js(route('admin.kuota.destroy', $k)), message: 'Hapus kuota ini? Tindakan ini tidak bisa dibatalkan.' })"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-ui-empty-state :colspan="9" message="Belum ada kuota." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($kuota->hasPages())
                    <div class="border-t border-gray-100 px-6 py-3">{{ $kuota->links() }}</div>
                @endif
            </x-ui-card>
        </div>

        <x-crud-modal create-title="Tambah Kuota" edit-title="Edit Kuota">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-ui-label for="modal-tahun_id" required>Tahun Penerimaan</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="tahun_id" id="modal-tahun_id" x-model="form.tahun_id">
                            <option value="">-- Pilih Tahun --</option>
                            @foreach ($tahunList as $t)
                                <option value="{{ $t->id }}">{{ $t->kode }} ({{ $t->status }})</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-jalur_id" required>Jalur</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="jalur_id" id="modal-jalur_id" x-model="form.jalur_id">
                            <option value="">-- Pilih Jalur --</option>
                            @foreach ($jalurList as $j)
                                <option value="{{ $j->id }}">{{ $j->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-prodi_id" required>Program Studi</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="prodi_id" id="modal-prodi_id" x-model="form.prodi_id">
                            <option value="">-- Pilih Prodi --</option>
                            @foreach ($prodiList as $p)
                                <option value="{{ $p->id }}">{{ $p->jenjang ? $p->jenjang.' - ' : '' }}{{ $p->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-kelas_id">Kelas Perkuliahan (opsional)</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="kelas_id" id="modal-kelas_id" x-model="form.kelas_id">
                            <option value="">-- Semua Kelas --</option>
                            @foreach ($kelasList as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-jumlah" required>Jumlah Kuota</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" name="jumlah" id="modal-jumlah" x-model.number="form.jumlah" min="0" />
                    </div>
                    <p class="mt-1 text-xs text-amber-600" x-show="mode === 'edit' && form.terpakai > 0" x-text="'Sudah terpakai ' + form.terpakai + ' kursi — jumlah tidak boleh kurang dari ini.'"></p>
                </div>
            </div>

            <x-ui-toggle name="is_active" id="modal-is_active" x-model="form.is_active" label="Buka" description="Kuota aktif dan dapat diisi pendaftar." />
        </x-crud-modal>
    </div>
@endsection
