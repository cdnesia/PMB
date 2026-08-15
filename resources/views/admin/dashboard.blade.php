@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-3 xl:grid-cols-5">
        @php
            $cards = [
                ['label' => 'Program Studi', 'value' => $stats['prodi'], 'icon' => 'academic', 'color' => 'bg-indigo-50 text-indigo-600'],
                ['label' => 'Jalur', 'value' => $stats['jalur'], 'icon' => 'route', 'color' => 'bg-sky-50 text-sky-600'],
                ['label' => 'Kelas Perkuliahan', 'value' => $stats['kelas'], 'icon' => 'square-stack', 'color' => 'bg-violet-50 text-violet-600'],
                ['label' => 'Kuota', 'value' => $stats['kuota'], 'icon' => 'chart', 'color' => 'bg-emerald-50 text-emerald-600'],
                ['label' => 'Pendaftar', 'value' => $stats['pendaftaran'], 'icon' => 'user', 'color' => 'bg-amber-50 text-amber-600'],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg {{ $card['color'] }}">
                        <x-icon :name="$card['icon']" class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $card['value'] }}</div>
                        <div class="text-xs text-gray-500">{{ $card['label'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Ringkasan tindakan --}}
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $summary = [
                ['label' => 'Perlu Konfirmasi Bayar', 'value' => $pendaftarSummary['belum_bayar'], 'color' => 'text-red-600', 'icon' => 'credit-card', 'route' => route('admin.pendaftar.index', ['status_pembayaran' => 'belum_bayar'])],
                ['label' => 'Menunggu Verifikasi Berkas', 'value' => $pendaftarSummary['menunggu_verifikasi'], 'color' => 'text-amber-600', 'icon' => 'document', 'route' => route('admin.pendaftar.index', ['status' => 'lunas'])],
                ['label' => 'Terverifikasi', 'value' => $pendaftarSummary['terverifikasi'], 'color' => 'text-sky-600', 'icon' => 'check', 'route' => route('admin.pendaftar.index', ['status' => 'terverifikasi'])],
                ['label' => 'Lolos Seleksi', 'value' => $pendaftarSummary['lolos'], 'color' => 'text-emerald-600', 'icon' => 'academic', 'route' => route('admin.pendaftar.index', ['status' => 'lolos'])],
            ];
        @endphp

        @foreach ($summary as $s)
            <a href="{{ $s['route'] }}" class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold {{ $s['color'] }}">{{ $s['value'] }}</div>
                        <div class="mt-1 text-xs text-gray-500">{{ $s['label'] }}</div>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 text-gray-400">
                        <x-icon :name="$s['icon']" class="h-5 w-5" />
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 xl:col-span-2">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Keterisian Kuota Terbaru</h2>
                    <p class="text-sm text-gray-500">Pantau kuota per kombinasi jalur, prodi, dan kelas.</p>
                </div>
                <a href="{{ route('admin.kuota.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Lihat semua →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Prodi</th>
                            <th class="px-6 py-3">Jalur</th>
                            <th class="px-6 py-3">Kelas</th>
                            <th class="px-6 py-3 text-right">Jumlah</th>
                            <th class="px-6 py-3 text-right">Terpakai</th>
                            <th class="px-6 py-3 text-right">Sisa</th>
                            <th class="px-6 py-3">Keterisian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($kuota as $k)
                            @php
                                $pct = $k->jumlah > 0 ? round($k->terpakai / $k->jumlah * 100) : 0;
                                $barColor = $pct >= 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-amber-500' : 'bg-emerald-500');
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $k->prodi?->jenjang ? $k->prodi->jenjang.' - ' : '' }}{{ $k->prodi?->nama }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $k->jalur?->nama }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $k->kelas?->nama ?? 'Semua kelas' }}</td>
                                <td class="px-6 py-3 text-right text-gray-900">{{ $k->jumlah }}</td>
                                <td class="px-6 py-3 text-right text-gray-600">{{ $k->terpakai }}</td>
                                <td class="px-6 py-3 text-right font-medium text-gray-900">{{ $k->sisa }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-100">
                                            <div class="h-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $pct }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-ui-empty-state :colspan="7" message="Belum ada data kuota." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Pendaftar Terbaru</h2>
                    <p class="text-sm text-gray-500">Daftar pendaftar terkini.</p>
                </div>
                <a href="{{ route('admin.pendaftar.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Semua →</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse ($pendaftarTerbaru as $p)
                    <a href="{{ route('admin.pendaftar.show', $p) }}" class="flex items-center justify-between px-6 py-3 transition hover:bg-gray-50">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-medium text-gray-900">{{ $p->user?->name }}</div>
                            <div class="truncate text-xs text-gray-500">{{ $p->nomor_pendaftaran }} · {{ $p->jalur?->nama }}</div>
                        </div>
                        <x-ui-status-badge :status="$p->status" />
                    </a>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-400">Belum ada pendaftar.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
