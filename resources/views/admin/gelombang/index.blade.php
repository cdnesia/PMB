@extends('layouts.admin')

@section('title', 'Gelombang')

@section('content')
    <x-ui-page-header title="Gelombang" description="Kelola gelombang pendaftaran beserta rentang tanggal dan jalurnya.">
        <x-slot:action>
            <x-ui-button variant="primary" :href="route('admin.gelombang.create')" icon="plus">Tambah Gelombang</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <x-ui-card>
        <form method="GET" action="{{ route('admin.gelombang.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div class="sm:col-span-2">
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
                <x-ui-button variant="secondary" type="button" :href="route('admin.gelombang.index')">Reset</x-ui-button>
                <x-ui-button variant="primary" type="submit">Filter</x-ui-button>
            </div>
        </form>
    </x-ui-card>

    <x-ui-card :padding="''" class="mt-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Gelombang</th>
                        <th class="px-6 py-3">Tahun</th>
                        <th class="px-6 py-3">Periode Pendaftaran</th>
                        <th class="px-6 py-3">Pengumuman</th>
                        <th class="px-6 py-3">Jalur</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($gelombang as $g)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $g->nama }}</td>
                            <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $g->tahun?->kode }}</td>
                            <td class="px-6 py-3 text-gray-600">
                                {{ $g->tanggal_mulai?->format('d/m/Y') }} — {{ $g->tanggal_selesai?->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $g->tanggal_pengumuman?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($g->jalur as $j)
                                        <x-ui-badge color="indigo">{{ $j->nama }}</x-ui-badge>
                                    @empty
                                        <span class="text-xs text-gray-400">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <x-ui-badge :color="$g->is_active ? 'green' : 'gray'">{{ $g->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.gelombang.edit', $g) }}" class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.gelombang.destroy', $g) }}">
                                        @csrf @method('DELETE')
                                        <button type="button" x-data x-on:click="$dispatch('confirm-delete', { form: $el.closest('form'), message: 'Hapus gelombang \'{{ $g->nama }}\'? Tindakan ini tidak bisa dibatalkan.' })" class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="7" message="Belum ada gelombang." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($gelombang->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $gelombang->links() }}</div>
        @endif
    </x-ui-card>
@endsection
