@extends('layouts.referrer')

@section('title', 'Profil & Rekening')

@section('content')
    <x-ui-page-header title="Profil & Rekening" description="Kelola data diri dan informasi rekening untuk pencairan komisi referral." />

    <form method="POST" action="{{ route('referrer.profile.update') }}">
        @csrf
        @method('PUT')

        <x-ui-card>
            <h2 class="text-base font-semibold text-gray-900">Profil</h2>

            <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-ui-label for="name" required>Nama</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="name" id="name" :value="old('name', $user->name)" placeholder="Nama lengkap" />
                    </div>
                    @error('name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui-label for="email">Email</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="email" name="email" id="email" :value="$user->email" :disabled="true" />
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">Hubungi admin untuk mengubah email.</p>
                </div>

                <div>
                    <x-ui-label for="phone">Telepon</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="phone" id="phone" :value="old('phone', $user->phone)" placeholder="08123456789" />
                    </div>
                    @error('phone')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui-label for="kode">Kode Referral</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="kode" id="kode" :value="$referrer->kode" :disabled="true" />
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">Hubungi admin untuk mengubah kode referral.</p>
                </div>

                <div>
                    <x-ui-label for="nama_instansi">Nama Instansi</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama_instansi" id="nama_instansi" :value="old('nama_instansi', $referrer->nama_instansi)" placeholder="Opsional, untuk mitra" />
                    </div>
                    @error('nama_instansi')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui-label for="password">Password Baru</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="password" name="password" id="password" placeholder="Kosongkan jika tidak diubah" />
                    </div>
                    @error('password')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui-label for="password_confirmation">Konfirmasi Password Baru</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="password" name="password_confirmation" id="password_confirmation" placeholder="Ulangi password baru" />
                    </div>
                </div>
            </div>
        </x-ui-card>

        <x-ui-card class="mt-6">
            <h2 class="text-base font-semibold text-gray-900">Informasi Rekening</h2>
            <p class="mt-1 text-xs text-gray-500">Digunakan admin untuk mencairkan komisi referral Anda.</p>

            <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <x-ui-label for="nama_bank">Nama Bank</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama_bank" id="nama_bank" :value="old('nama_bank', $referrer->nama_bank)" placeholder="Contoh: BCA, BRI, Mandiri" />
                    </div>
                    @error('nama_bank')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui-label for="nomor_rekening">Nomor Rekening</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nomor_rekening" id="nomor_rekening" :value="old('nomor_rekening', $referrer->nomor_rekening)" placeholder="Nomor rekening bank" />
                    </div>
                    @error('nomor_rekening')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui-label for="nama_pemilik_rekening">Nama Pemilik Rekening</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama_pemilik_rekening" id="nama_pemilik_rekening" :value="old('nama_pemilik_rekening', $referrer->nama_pemilik_rekening)" placeholder="Sesuai buku tabungan" />
                    </div>
                    @error('nama_pemilik_rekening')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('referrer.dashboard')">Batal</x-ui-button>
            </div>
        </x-ui-card>
    </form>
@endsection
