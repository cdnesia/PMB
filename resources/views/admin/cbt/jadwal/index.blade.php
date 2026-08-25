@extends('layouts.admin')

@section('title', 'Jadwal CBT')

@section('content')
    <x-ui-page-header title="Jadwal CBT" description="Kelola jadwal pelaksanaan tes CBT per jalur.">
        <x-slot:action>
            <x-ui-button variant="primary" :href="route('admin.cbt-jadwal.create')" icon="plus">Tambah Jadwal</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <x-ui-card>
        <form method="GET" action="{{ route('admin.cbt-jadwal.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div class="sm:col-span-2">
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
            <div class="flex items-end gap-2">
                <x-ui-button variant="primary" type="submit">Filter</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.cbt-jadwal.index')">Reset</x-ui-button>
            </div>
        </form>
    </x-ui-card>

    <x-ui-card :padding="''" class="mt-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Jadwal</th>
                        <th class="px-6 py-3">Jalur</th>
                        <th class="px-6 py-3">Target Prodi</th>
                        <th class="px-6 py-3">Waktu Pelaksanaan</th>
                        <th class="px-6 py-3">Durasi</th>
                        <th class="px-6 py-3">Komposisi Soal</th>
                        <th class="px-6 py-3">Peserta</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($jadwal as $j)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $j->nama }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $j->jalur?->nama }}</td>
                            <td class="px-6 py-3">
                                @if ($j->prodi)
                                    <x-ui-badge color="indigo">{{ $j->prodi->nama }}</x-ui-badge>
                                @else
                                    <span class="text-xs text-gray-400">Umum</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-gray-600">
                                {{ $j->waktu_mulai?->format('d/m/Y H:i') }} — {{ $j->waktu_selesai?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $j->durasi_menit }} menit</td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($j->komposisi as $k)
                                        <x-ui-badge color="blue">{{ $k->kategori }}: {{ $k->jumlah }}{{ $k->jumlah_prodi > 0 ? '+'.$k->jumlah_prodi.' prodi' : '' }}</x-ui-badge>
                                    @endforeach
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $j->totalSoalUmum() }} soal umum
                                    @if ($j->totalSoalProdiMaksimum() > 0)
                                        (+hingga {{ $j->totalSoalProdiMaksimum() }} khusus prodi)
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <a href="{{ route('admin.cbt-jadwal.peserta', $j) }}" class="font-medium text-indigo-600 hover:underline">{{ $j->sesi_count }} peserta</a>
                            </td>
                            <td class="px-6 py-3">
                                <x-ui-badge :color="$j->is_active ? 'green' : 'gray'">{{ $j->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.cbt-jadwal.edit', $j) }}" class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.cbt-jadwal.destroy', $j) }}" onsubmit="return confirm('Hapus jadwal \"{{ $j->nama }}\"?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="9" message="Belum ada jadwal CBT." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($jadwal->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $jadwal->links() }}</div>
        @endif
    </x-ui-card>
@endsection
