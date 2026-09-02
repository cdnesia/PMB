@extends('layouts.admin')

@section('title', 'Manajemen User')

@section('content')
    <x-ui-page-header title="Manajemen User" description="Kelola akun panitia, karyawan, mitra, dan mahasiswa.">
        <x-slot:action>
            <x-ui-button variant="primary" :href="route('admin.user.create')" icon="plus">Tambah User</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <x-ui-card>
        <form method="GET" action="{{ route('admin.user.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-ui-label for="search">Cari</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="search" id="search" :value="request('search')" placeholder="Nama / Email" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="role">Role</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="role" id="role">
                            <option value="">-- Semua Role --</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r }}" @selected(request('role') === $r)>{{ ucfirst(str_replace('-', ' ', $r)) }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <x-ui-button variant="secondary" type="button" :href="route('admin.user.index')">Reset</x-ui-button>
                <x-ui-button variant="primary" type="submit">Filter</x-ui-button>
            </div>
        </form>
    </x-ui-card>

    <x-ui-card :padding="''" class="mt-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Telepon</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3">Kode Referral</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $u)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $u->name }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $u->email }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $u->phone ?? '—' }}</td>
                            <td class="px-6 py-3">
                                @foreach ($u->roles as $role)
                                    <x-ui-badge color="indigo">{{ ucfirst(str_replace('-', ' ', $role->name)) }}</x-ui-badge>
                                @endforeach
                            </td>
                            <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ $u->referrerProfile?->kode ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.user.edit', $u) }}" class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.user.destroy', $u) }}">
                                        @csrf @method('DELETE')
                                        <button type="button" x-data x-on:click="$dispatch('confirm-delete', { form: $el.closest('form'), message: 'Hapus user \'{{ $u->name }}\' beserta seluruh data pendaftaran, riwayat pembayaran, hasil CBT, dan file dokumen miliknya? Tindakan ini tidak bisa dibatalkan.' })" class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="6" message="Belum ada user." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $users->links() }}</div>
        @endif
    </x-ui-card>
@endsection
