@extends('layouts.admin')

@section('title', 'Sumber Informasi')

@section('content')
    <x-ui-page-header title="Sumber Informasi" description="Kelola pilihan 'Dari mana Anda tahu tentang UM Jambi?' yang tampil saat registrasi akun.">
        <x-slot:action>
            <x-ui-button variant="primary" :href="route('admin.sumber-informasi.create')" icon="plus">Tambah Sumber</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <x-ui-card :padding="''">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Urutan</th>
                        <th class="px-6 py-3">Kode</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($sumberInformasi as $s)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-xs text-gray-500">{{ $s->urutan }}</td>
                            <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $s->kode }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $s->nama }}</td>
                            <td class="px-6 py-3">
                                <x-ui-badge :color="$s->is_active ? 'green' : 'gray'">{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.sumber-informasi.edit', $s) }}" class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.sumber-informasi.destroy', $s) }}">
                                        @csrf @method('DELETE')
                                        <button type="button" x-data x-on:click="$dispatch('confirm-delete', { form: $el.closest('form'), message: 'Hapus sumber informasi \'{{ $s->nama }}\'? Tindakan ini tidak bisa dibatalkan.' })" class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="5" message="Belum ada sumber informasi." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($sumberInformasi->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $sumberInformasi->links() }}</div>
        @endif
    </x-ui-card>
@endsection
