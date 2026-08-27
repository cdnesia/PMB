@extends('layouts.admin')

@section('title', 'Jalur & Biaya')

@section('content')
    <x-ui-page-header title="Jalur & Biaya" description="Kelola jalur penerimaan beserta biaya pendaftarannya.">
        <x-slot:action>
            <x-ui-button variant="primary" :href="route('admin.jalur.create')" icon="plus">Tambah Jalur</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <x-ui-card :padding="''">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Kode</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Kategori</th>
                        <th class="px-6 py-3">Urutan</th>
                        <th class="px-6 py-3">Biaya per Kelas</th>
                        <th class="px-6 py-3">Tes CBT</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($jalur as $j)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $j->kode }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $j->nama }}</td>
                            <td class="px-6 py-3">
                                <x-ui-badge :color="$j->kategori === 'nasional' ? 'blue' : 'indigo'">{{ ucfirst($j->kategori) }}</x-ui-badge>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $j->urutan }}</td>
                            <td class="px-6 py-3">
                                @if ($j->kelasBiaya->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($j->kelasBiaya->sortBy(fn ($b) => $b->kelas?->nama) as $b)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                                {{ $b->kelas?->nama }}: Rp {{ number_format($b->biaya_pendaftaran, 0, ',', '.') }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if ($j->requires_cbt)
                                    <x-ui-badge color="amber">CBT</x-ui-badge>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <x-ui-badge :color="$j->is_active ? 'green' : 'gray'">{{ $j->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.jalur.edit', $j) }}" class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.jalur.destroy', $j) }}">
                                        @csrf @method('DELETE')
                                        <button type="button" x-data x-on:click="$dispatch('confirm-delete', { form: $el.closest('form'), message: 'Hapus jalur \'{{ $j->nama }}\'? Tindakan ini tidak bisa dibatalkan.' })" class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="8" message="Belum ada jalur." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($jalur->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $jalur->links() }}</div>
        @endif
    </x-ui-card>
@endsection
