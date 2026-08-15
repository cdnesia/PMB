@extends('layouts.admin')

@section('title', 'Kelas Perkuliahan')

@section('content')
    <x-ui-page-header title="Kelas Perkuliahan" description="Kelola kelas perkuliahan (Reguler A, Reguler B, Kelas Karyawan, dll).">
        <x-slot:action>
            <x-ui-button variant="primary" :href="route('admin.kelas.create')" icon="plus">Tambah Kelas</x-ui-button>
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
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($kelas as $k)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $k->kode }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $k->nama }}</td>
                            <td class="px-6 py-3">
                                <x-ui-badge :color="$k->is_active ? 'green' : 'gray'">{{ $k->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.kelas.edit', $k) }}" class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.kelas.destroy', $k) }}" onsubmit="return confirm('Hapus kelas \"{{ $k->nama }}\"?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="4" message="Belum ada kelas perkuliahan." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($kelas->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $kelas->links() }}</div>
        @endif
    </x-ui-card>
@endsection
