@extends('layouts.admin')

@section('title', 'Pendaftar')

@section('content')
    <x-ui-page-header title="Pendaftar" description="Kelola dan proses seluruh pendaftaran mahasiswa baru." />

    @include('admin.pendaftar.partials.status-legend')

    <x-ui-card class="mt-6">
        <form method="GET" action="{{ route('admin.pendaftar.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <x-ui-label for="q">Cari</x-ui-label>
                <div class="mt-2">
                    <x-ui-input name="q" id="q" :value="request('q')" placeholder="Nama / Email / No. Pendaftaran" />
                </div>
            </div>

            <div>
                <x-ui-label for="jalur_id">Jalur</x-ui-label>
                <div class="mt-2">
                    <x-ui-select name="jalur_id" id="jalur_id">
                        <option value="">-- Semua Jalur --</option>
                        @foreach ($jalurList as $j)
                            <option value="{{ $j->id }}" @selected(request('jalur_id') == $j->id)>{{ $j->nama }}</option>
                        @endforeach
                    </x-ui-select>
                </div>
            </div>

            <div>
                <x-ui-label for="prodi_id">Prodi</x-ui-label>
                <div class="mt-2">
                    <x-ui-select name="prodi_id" id="prodi_id">
                        <option value="">-- Semua Prodi --</option>
                        @foreach ($prodiList as $p)
                            <option value="{{ $p->id }}" @selected(request('prodi_id') == $p->id)>{{ $p->jenjang ? $p->jenjang.' - ' : '' }}{{ $p->nama }}</option>
                        @endforeach
                    </x-ui-select>
                </div>
            </div>

            <div>
                <x-ui-label for="status">Status</x-ui-label>
                <div class="mt-2">
                    <x-ui-select name="status" id="status">
                        <option value="">-- Semua Status --</option>
                        @foreach (['draft', 'menunggu_pembayaran', 'lunas', 'terverifikasi', 'lolos', 'cadangan', 'tidak_lolos', 'daftar_ulang', 'mahasiswa_baru', 'ditolak'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                        @endforeach
                    </x-ui-select>
                </div>
            </div>

            <div class="flex items-end gap-2">
                <x-ui-button variant="primary" type="submit">Filter</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.pendaftar.index')">Reset</x-ui-button>
            </div>
        </form>
    </x-ui-card>

    <x-ui-card :padding="''" class="mt-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">No. Pendaftaran</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Jalur</th>
                        <th class="px-6 py-3">Pilihan Prodi</th>
                        <th class="px-6 py-3">Pembayaran</th>
                        <th class="px-6 py-3">Nilai</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pendaftaran as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $p->nomor_pendaftaran }}</td>
                            <td class="px-6 py-3">
                                <div class="font-medium text-gray-900">{{ $p->user?->name }}</div>
                                <div class="text-xs text-gray-500">{{ $p->user?->email }}</div>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $p->jalur?->nama }}</td>
                            <td class="px-6 py-3 text-gray-600">
                                @foreach ($p->prodiPilihan as $pp)
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs text-gray-400">{{ $pp->urutan }}.</span>
                                        <span>{{ $pp->prodi?->jenjang ? $pp->prodi->jenjang.' - ' : '' }}{{ $pp->prodi?->nama }}</span>
                                    </div>
                                @endforeach
                            </td>
                            <td class="px-6 py-3">
                                <x-ui-status-badge :status="$p->status_pembayaran" />
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $p->nilai_seleksi !== null ? number_format((float) $p->nilai_seleksi, 2, ',', '.') : '—' }}</td>
                            <td class="px-6 py-3">
                                <x-ui-status-badge :status="$p->status" />
                            </td>
                            <td class="px-6 py-3 text-right">
                                <x-ui-button variant="secondary" size="sm" :href="route('admin.pendaftar.show', $p)" icon="eye">Proses</x-ui-button>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="8" message="Belum ada pendaftar." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pendaftaran->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $pendaftaran->links() }}</div>
        @endif
    </x-ui-card>
@endsection
