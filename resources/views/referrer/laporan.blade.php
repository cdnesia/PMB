@extends('layouts.referrer')

@section('title', 'Laporan Referral')

@section('content')
    <x-ui-page-header title="Laporan Referral" description="Lihat riwayat mahasiswa yang mendaftar dengan kode referral Anda per tahun penerimaan." />

    <x-ui-card>
        <form method="GET" action="{{ route('referrer.laporan.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-full sm:w-64">
                <x-ui-label for="tahun_id">Tahun Penerimaan</x-ui-label>
                <div class="mt-2">
                    <x-ui-select name="tahun_id" id="tahun_id">
                        <option value="">-- Semua Tahun --</option>
                        @foreach ($tahunList as $t)
                            <option value="{{ $t->id }}" @selected($tahunId == $t->id)>
                                {{ $t->kode }}@if ($t->status === 'aktif') (Aktif) @endif
                            </option>
                        @endforeach
                    </x-ui-select>
                </div>
            </div>
            <x-ui-button variant="primary" type="submit">Filter</x-ui-button>
            <x-ui-button variant="secondary" type="button" :href="route('referrer.laporan.index')">Reset</x-ui-button>
        </form>
    </x-ui-card>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-ui-card>
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Mahasiswa</div>
            <div class="mt-1 text-2xl font-bold text-gray-900">{{ $pendaftar->count() }}</div>
        </x-ui-card>

        <x-ui-card>
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Sudah Lunas</div>
            <div class="mt-1 text-2xl font-bold text-emerald-600">{{ $pendaftar->where('status_pembayaran', 'lunas')->count() }}</div>
        </x-ui-card>

        <x-ui-card>
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Lolos Seleksi</div>
            <div class="mt-1 text-2xl font-bold text-indigo-600">{{ $pendaftar->where('status', 'lolos')->count() }}</div>
        </x-ui-card>
    </div>

    <div class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="border-b border-gray-100 px-6 py-4">
            <h2 class="text-base font-semibold text-gray-900">Mahasiswa yang Menggunakan Kode Referral</h2>
            <p class="text-sm text-gray-500">Daftar pendaftar beserta status pembayarannya.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Nomor Pendaftaran</th>
                        <th class="px-6 py-3">Tanggal Daftar</th>
                        <th class="px-6 py-3">Status Pembayaran</th>
                        <th class="px-6 py-3">Status Pendaftaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pendaftar as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $p->user?->name }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $p->nomor_pendaftaran ?? '-' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $p->created_at->translatedFormat('d M Y') }}</td>
                            <td class="px-6 py-3"><x-ui-status-badge :status="$p->status_pembayaran" /></td>
                            <td class="px-6 py-3"><x-ui-status-badge :status="$p->status" /></td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="5" message="Belum ada mahasiswa yang mendaftar dengan kode referral ini." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
