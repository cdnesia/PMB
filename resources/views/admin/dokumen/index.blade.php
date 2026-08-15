@extends('layouts.admin')

@section('title', 'Dokumen Persyaratan')

@section('content')
    <x-ui-page-header title="Dokumen Persyaratan" description="Kelola dokumen yang wajib/opsional diunggah per jalur dan prodi.">
        <x-slot:action>
            <x-ui-button variant="primary" :href="route('admin.dokumen.create')" icon="plus">Tambah Dokumen</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <x-ui-card>
        <form method="GET" action="{{ route('admin.dokumen.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
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

            <div>
                <x-ui-label for="scope">Kategori</x-ui-label>
                <div class="mt-2">
                    <x-ui-select name="scope" id="scope">
                        <option value="">-- Semua --</option>
                        <option value="jalur" @selected(request('scope') === 'jalur')>Berdasarkan Jalur</option>
                        <option value="prodi" @selected(request('scope') === 'prodi')>Berdasarkan Prodi</option>
                    </x-ui-select>
                </div>
            </div>

            <div class="flex items-end gap-2">
                <x-ui-button variant="primary" type="submit">Filter</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.dokumen.index')">Reset</x-ui-button>
            </div>
        </form>
    </x-ui-card>

    <x-ui-card :padding="''" class="mt-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Jalur</th>
                        <th class="px-6 py-3">Prodi</th>
                        <th class="px-6 py-3">Nama Dokumen</th>
                        <th class="px-6 py-3">Sifat</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($dokumen as $d)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-gray-600">{{ $d->jalur?->nama ?? '-' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $d->prodi?->jenjang ? $d->prodi->jenjang.' - ' : '' }}{{ $d->prodi?->nama ?? '-' }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $d->nama }}</td>
                            <td class="px-6 py-3">
                                <x-ui-badge :color="$d->wajib ? 'red' : 'gray'">{{ $d->wajib ? 'Wajib' : 'Opsional' }}</x-ui-badge>
                            </td>
                            <td class="px-6 py-3">
                                <x-ui-badge :color="$d->is_active ? 'green' : 'gray'">{{ $d->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.dokumen.edit', $d) }}" class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.dokumen.destroy', $d) }}" onsubmit="return confirm('Hapus dokumen \"{{ $d->nama }}\"?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="6" message="Belum ada dokumen persyaratan." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($dokumen->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $dokumen->links() }}</div>
        @endif
    </x-ui-card>
@endsection
