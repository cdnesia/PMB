@extends('layouts.admin')

@section('title', 'Tahun Penerimaan')

@section('content')
    <x-ui-page-header title="Tahun Penerimaan" description="Kelola periode tahun penerimaan mahasiswa baru.">
        <x-slot:action>
            <x-ui-button variant="primary" :href="route('admin.tahun.create')" icon="plus">Tambah Tahun</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <x-ui-card :padding="''">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Kode</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Periode</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($tahun as $t)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $t->kode }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $t->nama }}</td>
                            <td class="px-6 py-3">
                                <x-ui-status-badge :status="$t->status" />
                            </td>
                            <td class="px-6 py-3 text-gray-600">
                                @if ($t->tanggal_mulai)
                                    {{ $t->tanggal_mulai->format('d/m/Y') }} — {{ $t->tanggal_selesai?->format('d/m/Y') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.tahun.edit', $t) }}" class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.tahun.destroy', $t) }}">
                                        @csrf @method('DELETE')
                                        <button type="button" x-data x-on:click="$dispatch('confirm-delete', { form: $el.closest('form'), message: 'Hapus tahun \'{{ $t->nama }}\'? Tindakan ini tidak bisa dibatalkan.' })" class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="5" message="Belum ada tahun penerimaan." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tahun->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $tahun->links() }}</div>
        @endif
    </x-ui-card>
@endsection
