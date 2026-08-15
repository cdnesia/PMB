@extends('layouts.admin')

@section('title', 'Laporan')

@section('content')
    <x-ui-page-header title="Rekap Pendaftaran" description="Ringkasan dan laporan penerimaan mahasiswa baru." />

    {{-- Ringkasan utama --}}
    <div class="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-3 xl:grid-cols-6">
        @php
            $cards = [
                ['label' => 'Total Pendaftar', 'value' => $ringkasan['total_pendaftar'], 'color' => 'text-gray-900'],
                ['label' => 'Lunas', 'value' => $ringkasan['lunas'], 'color' => 'text-emerald-600'],
                ['label' => 'Belum Bayar', 'value' => $ringkasan['belum_bayar'], 'color' => 'text-red-600'],
                ['label' => 'Terverifikasi', 'value' => $ringkasan['terverifikasi'], 'color' => 'text-sky-600'],
                ['label' => 'Lolos', 'value' => $ringkasan['lolos'], 'color' => 'text-emerald-600'],
                ['label' => 'Mahasiswa Baru', 'value' => $ringkasan['mahasiswa_baru'], 'color' => 'text-indigo-600'],
            ];
        @endphp

        @foreach ($cards as $c)
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <div class="text-2xl font-bold {{ $c['color'] }}">{{ $c['value'] }}</div>
                <div class="mt-1 text-xs text-gray-500">{{ $c['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        {{-- Rekap per jalur --}}
        <x-ui-card :padding="''">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="text-base font-semibold text-gray-900">Rekap per Jalur</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Jalur</th>
                            <th class="px-6 py-3 text-right">Pendaftar</th>
                            <th class="px-6 py-3 text-right">Lunas</th>
                            <th class="px-6 py-3 text-right">Lolos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($perJalur as $j)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $j->nama }}</td>
                                <td class="px-6 py-3 text-right text-gray-900">{{ $j->pendaftar }}</td>
                                <td class="px-6 py-3 text-right text-emerald-600">{{ $j->lunas }}</td>
                                <td class="px-6 py-3 text-right text-gray-900">{{ $j->lolos }}</td>
                            </tr>
                        @empty
                            <x-ui-empty-state :colspan="4" message="Belum ada data jalur." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui-card>

        {{-- Rekap per prodi --}}
        <x-ui-card :padding="''">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="text-base font-semibold text-gray-900">Rekap per Prodi</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Prodi</th>
                            <th class="px-6 py-3 text-right">Pendaftar</th>
                            <th class="px-6 py-3 text-right">Lolos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($perProdi as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $p->jenjang ? $p->jenjang.' - ' : '' }}{{ $p->nama }}</td>
                                <td class="px-6 py-3 text-right text-gray-900">{{ $p->pendaftar }}</td>
                                <td class="px-6 py-3 text-right text-emerald-600">{{ $p->lolos }}</td>
                            </tr>
                        @empty
                            <x-ui-empty-state :colspan="3" message="Belum ada data prodi." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui-card>
    </div>

    {{-- Rekap per status --}}
    <x-ui-card :padding="''" class="mt-6">
        <div class="border-b border-gray-100 px-6 py-4">
            <h2 class="text-base font-semibold text-gray-900">Rekap per Status</h2>
        </div>
        <div class="grid grid-cols-2 gap-4 p-6 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($statusRekap as $s)
                <div class="rounded-lg bg-gray-50 px-4 py-3">
                    <x-ui-status-badge :status="$s['status']" />
                    <div class="mt-2 text-xl font-bold text-gray-900">{{ $s['total'] }}</div>
                </div>
            @endforeach
        </div>
    </x-ui-card>
@endsection
