@extends('layouts.admin')

@section('title', 'Tahun Penerimaan')

@section('content')
    <div x-data="crudModal({
        storeUrl: @js(route('admin.tahun.store')),
        defaults: { kode: '', nama: '', status: 'draft', tanggal_mulai: '', tanggal_selesai: '' },
    })">
        <x-ui-page-header title="Tahun Penerimaan" description="Kelola periode tahun penerimaan mahasiswa baru.">
            <x-slot:action>
                <x-ui-button variant="primary" type="button" x-on:click="openCreate()" icon="plus">Tambah Tahun</x-ui-button>
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
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Periode</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($tahun as $t)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $t->kode }}</td>
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $t->nama }}</td>
                                    <td class="px-6 py-3">
                                        <x-ui-status-badge :status="$t->status" />
                                    </td>
                                    <td class="px-6 py-3 text-gray-600">
                                        @if ($t->tanggal_mulai)
                                            {{ $t->tanggal_mulai->format('d/m/Y') }} — {{ $t->tanggal_selesai?->format('d/m/Y') }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button"
                                                x-on:click="openEdit(@js([
                                                    'id' => $t->id,
                                                    '_updateUrl' => route('admin.tahun.update', $t),
                                                    'kode' => $t->kode,
                                                    'nama' => $t->nama,
                                                    'status' => $t->status,
                                                    'tanggal_mulai' => $t->tanggal_mulai?->format('Y-m-d'),
                                                    'tanggal_selesai' => $t->tanggal_selesai?->format('Y-m-d'),
                                                ]))"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </button>
                                            <button type="button"
                                                x-on:click="$dispatch('confirm-delete', { url: @js(route('admin.tahun.destroy', $t)), message: @js('Hapus tahun \''.$t->nama.'\'? Tindakan ini tidak bisa dibatalkan.') })"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-ui-empty-state :colspan="5" message="Belum ada tahun penerimaan." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($tahun->hasPages())
                    <div class="border-t border-gray-100 px-6 py-3">{{ $tahun->links() }}</div>
                @endif
            </x-ui-card>
        </div>

        <x-crud-modal create-title="Tambah Tahun Penerimaan" edit-title="Edit Tahun Penerimaan">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-ui-label for="modal-kode" required>Kode</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="kode" id="modal-kode" x-model="form.kode" placeholder="2026/2027" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-nama" required>Nama</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="modal-nama" x-model="form.nama" placeholder="Tahun Penerimaan 2026/2027" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-status" required>Status</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="status" id="modal-status" x-model="form.status" :searchable="false">
                            @foreach (['draft', 'aktif', 'ditutup', 'arsip'] as $s)
                                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
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
            </div>
        </x-crud-modal>
    </div>
@endsection
