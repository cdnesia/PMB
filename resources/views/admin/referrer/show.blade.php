@extends('layouts.admin')

@section('title', 'Detail Referrer')

@section('content')
    <x-ui-page-header :title="$referrer->kode" :description="$referrer->user?->name . ($referrer->nama_instansi ? ' · ' . $referrer->nama_instansi : '')">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.referrer.index')" icon="arrow-left">Kembali</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <div class="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-4">
        @php
            $cards = [
                ['label' => 'Jenis', 'value' => ucfirst($referrer->jenis), 'color' => 'text-gray-900'],
                ['label' => 'Total Mahasiswa', 'value' => $pendaftar->total(), 'color' => 'text-sky-600'],
                ['label' => 'Sudah Lunas', 'value' => $referrer->pendaftaran()->where('status_pembayaran', 'lunas')->count(), 'color' => 'text-emerald-600'],
                ['label' => 'Lolos Seleksi', 'value' => $referrer->pendaftaran()->where('status', 'lolos')->count(), 'color' => 'text-indigo-600'],
            ];
        @endphp

        @foreach ($cards as $c)
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <div class="text-2xl font-bold {{ $c['color'] }}">{{ $c['value'] }}</div>
                <div class="mt-1 text-xs text-gray-500">{{ $c['label'] }}</div>
            </div>
        @endforeach
    </div>

    <x-ui-card :padding="''" class="mt-6">
        <div class="border-b border-gray-100 px-6 py-4">
            <h2 class="text-base font-semibold text-gray-900">Mahasiswa yang Direferensikan</h2>
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
                        <x-ui-empty-state :colspan="5" message="Belum ada mahasiswa yang direferensikan." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pendaftar->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $pendaftar->links() }}</div>
        @endif
    </x-ui-card>
@endsection
