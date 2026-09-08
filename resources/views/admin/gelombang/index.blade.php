@extends('layouts.admin')

@section('title', 'Gelombang')

@section('content')
    <div x-data="crudModal({
        storeUrl: @js(route('admin.gelombang.store')),
        defaults: {
            tahun_id: '', nama: '', tanggal_mulai: '', tanggal_selesai: '', tanggal_pengumuman: '',
            jalur: [], is_active: true,
        },
    })">
        <x-ui-page-header title="Gelombang" description="Kelola gelombang pendaftaran beserta rentang tanggal dan jalurnya.">
            <x-slot:action>
                <x-ui-button variant="primary" type="button" x-on:click="openCreate()" icon="plus">Tambah Gelombang</x-ui-button>
            </x-slot:action>
        </x-ui-page-header>

        <x-ui-card>
            <form method="GET" action="{{ route('admin.gelombang.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div class="sm:col-span-2">
                        <x-ui-label for="tahun_id">Tahun Penerimaan</x-ui-label>
                        <div class="mt-2">
                            <x-ui-select name="tahun_id" id="tahun_id">
                                <option value="">-- Semua Tahun --</option>
                                @foreach ($tahunList as $t)
                                    <option value="{{ $t->id }}" @selected(request('tahun_id') == $t->id)>{{ $t->kode }}</option>
                                @endforeach
                            </x-ui-select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <x-ui-button variant="secondary" type="button" :href="route('admin.gelombang.index')">Reset</x-ui-button>
                    <x-ui-button variant="primary" type="submit">Filter</x-ui-button>
                </div>
            </form>
        </x-ui-card>

        <div id="crud-table" class="mt-6">
            <x-ui-card :padding="''">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Gelombang</th>
                                <th class="px-6 py-3">Tahun</th>
                                <th class="px-6 py-3">Periode Pendaftaran</th>
                                <th class="px-6 py-3">Pengumuman</th>
                                <th class="px-6 py-3">Jalur</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($gelombang as $g)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $g->nama }}</td>
                                    <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $g->tahun?->kode }}</td>
                                    <td class="px-6 py-3 text-gray-600">
                                        {{ $g->tanggal_mulai?->format('d/m/Y') }} — {{ $g->tanggal_selesai?->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-600">{{ $g->tanggal_pengumuman?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-6 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($g->jalur as $j)
                                                <x-ui-badge color="indigo">{{ $j->nama }}</x-ui-badge>
                                            @empty
                                                <span class="text-xs text-gray-400">—</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-3">
                                        <x-ui-badge :color="$g->is_active ? 'green' : 'gray'">{{ $g->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button"
                                                x-on:click="openEdit(@js([
                                                    'id' => $g->id,
                                                    '_updateUrl' => route('admin.gelombang.update', $g),
                                                    'tahun_id' => $g->tahun_id,
                                                    'nama' => $g->nama,
                                                    'tanggal_mulai' => $g->tanggal_mulai?->format('Y-m-d'),
                                                    'tanggal_selesai' => $g->tanggal_selesai?->format('Y-m-d'),
                                                    'tanggal_pengumuman' => $g->tanggal_pengumuman?->format('Y-m-d'),
                                                    'jalur' => $g->jalur->pluck('id'),
                                                    'is_active' => (bool) $g->is_active,
                                                ]))"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </button>
                                            <button type="button"
                                                x-on:click="$dispatch('confirm-delete', { url: @js(route('admin.gelombang.destroy', $g)), message: @js('Hapus gelombang \''.$g->nama.'\'? Tindakan ini tidak bisa dibatalkan.') })"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-ui-empty-state :colspan="7" message="Belum ada gelombang." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($gelombang->hasPages())
                    <div class="border-t border-gray-100 px-6 py-3">{{ $gelombang->links() }}</div>
                @endif
            </x-ui-card>
        </div>

        <x-crud-modal create-title="Tambah Gelombang" edit-title="Edit Gelombang" size="2xl">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-ui-label for="modal-tahun_id" required>Tahun Penerimaan</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="tahun_id" id="modal-tahun_id" x-model="form.tahun_id">
                            <option value="">-- Pilih Tahun --</option>
                            @foreach ($tahunList as $t)
                                <option value="{{ $t->id }}">{{ $t->kode }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-nama" required>Nama Gelombang</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="modal-nama" x-model="form.nama" placeholder="Gelombang 1" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-tanggal_mulai" required>Tanggal Mulai Pendaftaran</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="date" name="tanggal_mulai" id="modal-tanggal_mulai" x-model="form.tanggal_mulai" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-tanggal_selesai" required>Tanggal Selesai Pendaftaran</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="date" name="tanggal_selesai" id="modal-tanggal_selesai" x-model="form.tanggal_selesai" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-tanggal_pengumuman">Tanggal Pengumuman</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="date" name="tanggal_pengumuman" id="modal-tanggal_pengumuman" x-model="form.tanggal_pengumuman" />
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <h3 class="text-sm font-semibold text-gray-900">Jalur yang Tersedia</h3>
                <p class="mt-1 text-xs text-gray-500">Centang jalur yang berlaku di gelombang ini. Minimal satu jalur.</p>

                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach ($jalurList as $j)
                        <label class="flex cursor-pointer select-none items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 transition hover:bg-gray-50 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50">
                            <input type="checkbox" value="{{ $j->id }}" x-model="form.jalur" class="peer sr-only">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-white transition-colors peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-600 peer-focus-visible:ring-offset-2">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                            <span class="text-sm font-medium text-gray-700">{{ $j->nama }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <x-ui-toggle name="is_active" id="modal-is_active" x-model="form.is_active" label="Aktif" description="Gelombang sedang dibuka untuk pendaftaran." />
        </x-crud-modal>
    </div>
@endsection
