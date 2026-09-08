@extends('layouts.admin')

@section('title', 'Manajemen User')

@section('content')
    <div x-data="crudModal({
        storeUrl: @js(route('admin.user.store')),
        defaults: {
            name: '', email: '', phone: '', role: '', password: '', password_confirmation: '',
            kode: '', nama_instansi: '', nama_bank: '', nomor_rekening: '', nama_pemilik_rekening: '',
            referrer_is_active: true,
        },
    })">
        <x-ui-page-header title="Manajemen User" description="Kelola akun panitia, karyawan, mitra, dan mahasiswa.">
            <x-slot:action>
                <x-ui-button variant="primary" type="button" x-on:click="openCreate()" icon="plus">Tambah User</x-ui-button>
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

        <div id="crud-table" class="mt-6">
            <x-ui-card :padding="''">
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
                                            <button type="button"
                                                x-on:click="openEdit(@js([
                                                    'id' => $u->id,
                                                    '_updateUrl' => route('admin.user.update', $u),
                                                    'name' => $u->name,
                                                    'email' => $u->email,
                                                    'phone' => $u->phone,
                                                    'role' => $u->roles->first()?->name,
                                                    'password' => '',
                                                    'password_confirmation' => '',
                                                    'kode' => $u->referrerProfile?->kode,
                                                    'nama_instansi' => $u->referrerProfile?->nama_instansi,
                                                    'nama_bank' => $u->referrerProfile?->nama_bank,
                                                    'nomor_rekening' => $u->referrerProfile?->nomor_rekening,
                                                    'nama_pemilik_rekening' => $u->referrerProfile?->nama_pemilik_rekening,
                                                    'referrer_is_active' => (bool) ($u->referrerProfile?->is_active ?? true),
                                                ]))"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </button>
                                            <button type="button"
                                                x-on:click="$dispatch('confirm-delete', { url: @js(route('admin.user.destroy', $u)), message: @js('Hapus user \''.$u->name.'\' beserta seluruh data pendaftaran, riwayat pembayaran, hasil CBT, dan file dokumen miliknya? Tindakan ini tidak bisa dibatalkan.') })"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
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
        </div>

        <x-crud-modal create-title="Tambah User" edit-title="Edit User" size="2xl">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-ui-label for="modal-name" required>Nama</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="name" id="modal-name" x-model="form.name" placeholder="Nama lengkap" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-email" required>Email</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="email" name="email" id="modal-email" x-model="form.email" placeholder="nama@contoh.com" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-phone">Telepon</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="phone" id="modal-phone" x-model="form.phone" placeholder="08123456789" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-role" required>Role</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="role" id="modal-role" x-model="form.role">
                            <option value="">-- Pilih Role --</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r }}">{{ ucfirst(str_replace('-', ' ', $r)) }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-password" x-bind:required="mode === 'create'">Password</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="password" name="password" id="modal-password" x-model="form.password" x-bind:placeholder="mode === 'create' ? 'Minimal 8 karakter' : 'Kosongkan jika tidak diubah'" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-password_confirmation" x-bind:required="mode === 'create'">Konfirmasi Password</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="password" name="password_confirmation" id="modal-password_confirmation" x-model="form.password_confirmation" placeholder="Ulangi password" />
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-indigo-100 bg-indigo-50/50 p-4" x-show="form.role === 'karyawan' || form.role === 'mitra'" x-cloak>
                <h3 class="text-sm font-semibold text-gray-900">Profil Referral</h3>
                <p class="mt-1 text-xs text-gray-500">Kode referral yang akan digunakan mahasiswa saat mendaftar.</p>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-ui-label for="modal-kode">Kode Referral</x-ui-label>
                        <div class="mt-2">
                            <x-ui-input name="kode" id="modal-kode" x-model="form.kode" placeholder="REF-NAMA" />
                        </div>
                    </div>

                    <div>
                        <x-ui-label for="modal-nama_instansi">Nama Instansi</x-ui-label>
                        <div class="mt-2">
                            <x-ui-input name="nama_instansi" id="modal-nama_instansi" x-model="form.nama_instansi" placeholder="Opsional, untuk mitra" />
                        </div>
                    </div>

                    <div>
                        <x-ui-label for="modal-nama_bank">Nama Bank</x-ui-label>
                        <div class="mt-2">
                            <x-ui-input name="nama_bank" id="modal-nama_bank" x-model="form.nama_bank" placeholder="Contoh: BCA, BRI, Mandiri" />
                        </div>
                    </div>

                    <div>
                        <x-ui-label for="modal-nomor_rekening">Nomor Rekening</x-ui-label>
                        <div class="mt-2">
                            <x-ui-input name="nomor_rekening" id="modal-nomor_rekening" x-model="form.nomor_rekening" placeholder="Nomor rekening bank" />
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <x-ui-label for="modal-nama_pemilik_rekening">Nama Pemilik Rekening</x-ui-label>
                        <div class="mt-2">
                            <x-ui-input name="nama_pemilik_rekening" id="modal-nama_pemilik_rekening" x-model="form.nama_pemilik_rekening" placeholder="Sesuai buku tabungan" />
                        </div>
                    </div>

                    <div>
                        <x-ui-toggle name="referrer_is_active" id="modal-referrer_is_active" x-model="form.referrer_is_active" label="Aktif" description="Kode referral dapat digunakan mahasiswa." />
                    </div>
                </div>
            </div>
        </x-crud-modal>
    </div>
@endsection
