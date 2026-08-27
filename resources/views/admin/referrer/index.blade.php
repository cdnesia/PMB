@extends('layouts.admin')

@section('title', 'Rekap Referrer')

@section('content')
    <x-ui-page-header title="Rekap Referrer" description="Ringkasan performa karyawan & mitra dalam membawa pendaftar." />

    <div class="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-4">
        @php
            $cards = [
                ['label' => 'Total Referrer', 'value' => $ringkasan['total_referrer'], 'color' => 'text-gray-900'],
                ['label' => 'Referrer Aktif', 'value' => $ringkasan['aktif'], 'color' => 'text-indigo-600'],
                ['label' => 'Mahasiswa dari Referral', 'value' => $ringkasan['total_mahasiswa'], 'color' => 'text-sky-600'],
                ['label' => 'Sudah Lunas', 'value' => $ringkasan['total_lunas'], 'color' => 'text-emerald-600'],
            ];
        @endphp

        @foreach ($cards as $c)
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <div class="text-2xl font-bold {{ $c['color'] }}">{{ $c['value'] }}</div>
                <div class="mt-1 text-xs text-gray-500">{{ $c['label'] }}</div>
            </div>
        @endforeach
    </div>

    <x-ui-card class="mt-6">
        <form method="GET" action="{{ route('admin.referrer.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <x-ui-label for="search">Cari</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="search" id="search" :value="request('search')" placeholder="Kode / Instansi / Nama" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="jenis">Jenis</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="jenis" id="jenis">
                            <option value="">-- Semua Jenis --</option>
                            <option value="karyawan" @selected(request('jenis') === 'karyawan')>Karyawan</option>
                            <option value="mitra" @selected(request('jenis') === 'mitra')>Mitra</option>
                        </x-ui-select>
                    </div>
                </div>

                <div>
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
                <x-ui-button variant="secondary" type="button" :href="route('admin.referrer.index')">Reset</x-ui-button>
                <x-ui-button variant="primary" type="submit">Filter</x-ui-button>
            </div>
        </form>
    </x-ui-card>

    <x-ui-card :padding="''" class="mt-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Kode</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Jenis</th>
                        <th class="px-6 py-3">Instansi</th>
                        <th class="px-6 py-3 text-right">Mahasiswa</th>
                        <th class="px-6 py-3 text-right">Lunas</th>
                        <th class="px-6 py-3 text-right">Lolos</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($referrer as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $r->kode }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $r->user?->name }}</td>
                            <td class="px-6 py-3">
                                <x-ui-badge :color="$r->jenis === 'mitra' ? 'indigo' : 'blue'">{{ ucfirst($r->jenis) }}</x-ui-badge>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $r->nama_instansi ?? '—' }}</td>
                            <td class="px-6 py-3 text-right text-gray-900">{{ $r->pendaftaran_count }}</td>
                            <td class="px-6 py-3 text-right text-emerald-600">{{ $r->lunas_count }}</td>
                            <td class="px-6 py-3 text-right text-gray-900">{{ $r->lolos_count }}</td>
                            <td class="px-6 py-3">
                                <x-ui-badge :color="$r->is_active ? 'green' : 'gray'">{{ $r->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('admin.referrer.show', $r) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Detail →</a>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="9" message="Belum ada referrer." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($referrer->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $referrer->links() }}</div>
        @endif
    </x-ui-card>
@endsection
