@extends('layouts.admin')

@section('title', 'Rekap Lolos Seleksi')

@section('content')
    <x-ui-page-header title="Rekap Lolos Seleksi" description="Daftar mahasiswa yang dinyatakan lolos beserta prodi yang meluluskannya.">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.laporan.index')" icon="arrow-left">Kembali ke Rekap</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <x-ui-card class="mt-6">
        <form method="GET" action="{{ route('admin.laporan.lolos') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <x-ui-label for="q">Cari</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="q" id="q" :value="request('q')" placeholder="Nama / Email / No. Pendaftaran" />
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
            </div>

            <div class="flex items-center justify-end gap-2">
                <x-ui-button variant="secondary" type="button" :href="route('admin.laporan.lolos')">Reset</x-ui-button>
                <x-ui-button variant="primary" type="submit">Filter</x-ui-button>
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
                        <th class="px-6 py-3">Lolos di Prodi</th>
                        <th class="px-6 py-3">Pilihan Ke</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($lolos as $pp)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $pp->pendaftaran?->nomor_pendaftaran }}</td>
                            <td class="px-6 py-3">
                                <div class="font-medium text-gray-900">{{ $pp->pendaftaran?->user?->name }}</div>
                                <div class="text-xs text-gray-500">{{ $pp->pendaftaran?->user?->email }}</div>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $pp->pendaftaran?->jalur?->nama }}</td>
                            <td class="px-6 py-3 text-gray-900">
                                {{ $pp->prodi?->jenjang ? $pp->prodi->jenjang.' - ' : '' }}{{ $pp->prodi?->nama }}
                                <div class="text-xs text-gray-500">{{ $pp->kelas?->nama }}</div>
                            </td>
                            <td class="px-6 py-3 text-gray-600">
                                Pilihan {{ $pp->urutan }}
                            </td>
                            <td class="px-6 py-3 text-right">
                                @if ($pp->pendaftaran)
                                    <x-ui-button variant="secondary" size="sm" :href="route('admin.pendaftar.show', $pp->pendaftaran)" icon="eye">Detail</x-ui-button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="6" message="Belum ada mahasiswa yang lolos." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($lolos->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $lolos->links() }}</div>
        @endif
    </x-ui-card>
@endsection
