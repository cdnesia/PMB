@extends('layouts.admin')

@section('title', 'Program Studi')

@section('content')
    <x-ui-page-header title="Program Studi" description="Kelola program studi yang tersedia dalam penerimaan.">
        <x-slot:action>
            <x-ui-button variant="primary" :href="route('admin.prodi.create')" icon="plus">Tambah Prodi</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <x-ui-card :padding="''">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Kode</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Jenjang</th>
                        <th class="px-6 py-3">Fakultas</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($prodi as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $p->kode }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $p->nama }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $p->jenjang }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $p->fakultas ?? '-' }}</td>
                            <td class="px-6 py-3">
                                <x-ui-badge :color="$p->is_active ? 'green' : 'gray'">{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.prodi.edit', $p) }}" class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.prodi.destroy', $p) }}" onsubmit="return confirm('Hapus prodi \"{{ $p->nama }}\"?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="6" message="Belum ada program studi." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($prodi->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $prodi->links() }}</div>
        @endif
    </x-ui-card>
@endsection
