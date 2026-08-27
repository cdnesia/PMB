@extends('layouts.admin')

@section('title', 'Kuota Prodi')

@section('content')
    <x-ui-page-header title="Kuota Prodi" description="Atur jumlah kuota per tahun, jalur, prodi, dan kelas.">
        <x-slot:action>
            <x-ui-button variant="primary" :href="route('admin.kuota.create')" icon="plus">Tambah Kuota</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <x-ui-card :padding="''">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Tahun</th>
                        <th class="px-6 py-3">Jalur</th>
                        <th class="px-6 py-3">Prodi</th>
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3 text-right">Jumlah</th>
                        <th class="px-6 py-3 text-right">Terpakai</th>
                        <th class="px-6 py-3 text-right">Sisa</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($kuota as $k)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $k->tahun?->kode }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $k->jalur?->nama }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $k->prodi?->jenjang ? $k->prodi->jenjang.' - ' : '' }}{{ $k->prodi?->nama }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $k->kelas?->nama ?? 'Semua kelas' }}</td>
                            <td class="px-6 py-3 text-right text-gray-900">{{ $k->jumlah }}</td>
                            <td class="px-6 py-3 text-right text-gray-600">{{ $k->terpakai }}</td>
                            <td class="px-6 py-3 text-right font-medium {{ $k->sisa === 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $k->sisa }}</td>
                            <td class="px-6 py-3">
                                <x-ui-badge :color="$k->is_active ? 'green' : 'gray'">{{ $k->is_active ? 'Buka' : 'Tutup' }}</x-ui-badge>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.kuota.edit', $k) }}" class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.kuota.destroy', $k) }}">
                                        @csrf @method('DELETE')
                                        <button type="button" x-data x-on:click="$dispatch('confirm-delete', { form: $el.closest('form'), message: 'Hapus kuota ini? Tindakan ini tidak bisa dibatalkan.' })" class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="9" message="Belum ada kuota." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($kuota->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $kuota->links() }}</div>
        @endif
    </x-ui-card>
@endsection
