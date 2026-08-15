@extends('layouts.admin')

@section('title', 'Promo')

@section('content')
    <x-ui-page-header title="Promo" description="Kelola promo potongan biaya pendaftaran dan/atau SPP.">
        <x-slot:action>
            <x-ui-button variant="primary" :href="route('admin.promo.create')" icon="plus">Tambah Promo</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <x-ui-card :padding="''">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Kode</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Jenis</th>
                        <th class="px-6 py-3 text-right">Potongan</th>
                        <th class="px-6 py-3">Ketentuan (Jalur · Prodi · Kelas)</th>
                        <th class="px-6 py-3">Periode Berlaku</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($promo as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $p->kode }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $p->nama }}</td>
                            <td class="px-6 py-3">
                                <x-ui-badge :color="match ($p->jenis) { 'spp' => 'amber', 'semua' => 'indigo', default => 'blue' }">
                                    {{ $p->labelJenis() }}
                                </x-ui-badge>
                            </td>
                            <td class="px-6 py-3 text-right font-medium text-gray-900">{{ $p->labelPotongan() }}</td>
                            <td class="px-6 py-3 text-gray-600">
                                @if ($p->is_global)
                                    <x-ui-badge color="green">Global — semua kombinasi</x-ui-badge>
                                @elseif ($p->ketentuan->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($p->ketentuan as $k)
                                            <span class="rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700">
                                                {{ $k->jalur?->nama ?? '—' }} · {{ $k->prodi?->jenjang ? $k->prodi->jenjang.' - ' : '' }}{{ $k->prodi?->nama ?? '—' }} · {{ $k->kelas?->nama ?? '—' }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-gray-600">
                                @if ($p->tanggal_mulai || $p->tanggal_selesai)
                                    {{ $p->tanggal_mulai?->format('d/m/Y') ?? '—' }} s/d {{ $p->tanggal_selesai?->format('d/m/Y') ?? '—' }}
                                @else
                                    <span class="text-xs text-gray-400">Tanpa batas</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <x-ui-badge :color="$p->isBerlaku() ? 'green' : 'gray'">{{ $p->isBerlaku() ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.promo.edit', $p) }}" class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.promo.destroy', $p) }}" onsubmit="return confirm('Hapus promo \"{{ $p->nama }}\"?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="8" message="Belum ada promo." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($promo->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $promo->links() }}</div>
        @endif
    </x-ui-card>
@endsection
