@extends('layouts.admin')

@section('title', $user->exists ? 'Edit User' : 'Tambah User')

@section('content')
    @php
        $currentRole = old('role', $user->exists ? $user->roles->first()?->name : null);
    @endphp

    <x-ui-page-header :title="$user->exists ? 'Edit User' : 'Tambah User'">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.user.index')" icon="arrow-left">Kembali</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <form method="POST" action="{{ $user->exists ? route('admin.user.update', $user) : route('admin.user.store') }}"
          x-data="{ role: @js($currentRole) }">
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <x-ui-card>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-ui-label for="name" required>Nama</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="name" id="name" :value="old('name', $user->name)" placeholder="Nama lengkap" />
                    </div>
                    @error('name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui-label for="email" required>Email</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="email" name="email" id="email" :value="old('email', $user->email)" placeholder="nama@contoh.com" />
                    </div>
                    @error('email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui-label for="phone">Telepon</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="phone" id="phone" :value="old('phone', $user->phone)" placeholder="08123456789" />
                    </div>
                    @error('phone')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui-label for="role" required>Role</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="role" id="role" x-model="role">
                            <option value="">-- Pilih Role --</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r }}" @selected($currentRole === $r)>{{ ucfirst(str_replace('-', ' ', $r)) }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                    @error('role')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui-label for="password" :required="! $user->exists">Password</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="password" name="password" id="password" placeholder="{{ $user->exists ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}" />
                    </div>
                    @error('password')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui-label for="password_confirmation" :required="! $user->exists">Konfirmasi Password</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="password" name="password_confirmation" id="password_confirmation" placeholder="Ulangi password" />
                    </div>
                </div>
            </div>

            <div class="mt-6 rounded-lg border border-indigo-100 bg-indigo-50/50 p-4" x-show="role === 'karyawan' || role === 'mitra'" x-cloak>
                <h3 class="text-sm font-semibold text-gray-900">Profil Referral</h3>
                <p class="mt-1 text-xs text-gray-500">Kode referral yang akan digunakan mahasiswa saat mendaftar.</p>

                <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <x-ui-label for="kode">Kode Referral</x-ui-label>
                        <div class="mt-2">
                            <x-ui-input name="kode" id="kode" :value="old('kode', $user->referrerProfile?->kode)" placeholder="REF-NAMA" />
                        </div>
                        @error('kode')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-ui-label for="nama_instansi">Nama Instansi</x-ui-label>
                        <div class="mt-2">
                            <x-ui-input name="nama_instansi" id="nama_instansi" :value="old('nama_instansi', $user->referrerProfile?->nama_instansi)" placeholder="Opsional, untuk mitra" />
                        </div>
                    </div>

                    <div>
                        <x-ui-toggle name="referrer_is_active" id="referrer_is_active"
                                     :checked="old('referrer_is_active', $user->exists ? ($user->referrerProfile->is_active ?? true) : true)"
                                     label="Aktif" description="Kode referral dapat digunakan mahasiswa." />
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.user.index')">Batal</x-ui-button>
            </div>
        </x-ui-card>
    </form>
@endsection
