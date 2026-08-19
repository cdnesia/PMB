<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Buat Akun</h1>
        <p class="mt-2 text-sm leading-relaxed text-gray-500">Daftar untuk memulai perjalanan seleksi Anda.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5" x-data="{}">
        @csrf

        <div>
            <x-ui-label for="name" required>Nama Lengkap</x-ui-label>
            <div class="mt-2">
                <x-ui-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nama Anda" />
            </div>
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui-label for="email" required>Email</x-ui-label>
            <div class="mt-2">
                <x-ui-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nama@email.com" />
            </div>
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui-label for="phone" required>Nomor WA / Telepon</x-ui-label>
            <div class="mt-2">
                <x-ui-input id="phone" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" inputmode="numeric" placeholder="08xx xxxx xxxx" />
            </div>
            <p class="mt-1.5 text-xs text-gray-400">Gunakan nomor aktif untuk menerima informasi seleksi.</p>
            @error('phone')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui-label for="kode_referral">Kode Referral</x-ui-label>
            <div class="mt-2">
                <select name="kode_referral" id="kode_referral" x-select2="{
                        ajax: {
                            url: '{{ route('referral.search') }}',
                            dataType: 'json',
                            delay: 300,
                            data: (params) => ({ q: params.term }),
                            processResults: (data) => data,
                        },
                        minimumInputLength: 2,
                        allowClear: true,
                        language: {
                            searching: () => 'Mencari...',
                            inputTooShort: () => 'Ketik minimal 2 karakter',
                            noResults: () => 'Kode referral tidak ditemukan',
                        },
                        placeholder: 'Ketik untuk mencari kode referral (opsional)',
                    }"
                    class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    @if (old('kode_referral'))
                        <option value="{{ old('kode_referral') }}" selected>{{ old('kode_referral') }}</option>
                    @endif
                </select>
            </div>
            <p class="mt-1.5 text-xs text-gray-400">Punya kode dari karyawan atau mitra sekolah? Ketik kodenya, lalu pilih dari daftar.</p>
            @error('kode_referral')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div x-data="{ show: false }">
            <x-ui-label for="password" required>Password</x-ui-label>
            <div class="relative mt-2">
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password" placeholder="Min. 8 karakter"
                       class="block w-full rounded-lg border-0 py-2.5 pl-3 pr-11 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 transition hover:text-gray-600">
                    <svg x-show="!show" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg x-show="show" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div x-data="{ show: false }">
            <x-ui-label for="password_confirmation" required>Konfirmasi Password</x-ui-label>
            <div class="relative mt-2">
                <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password"
                       class="block w-full rounded-lg border-0 py-2.5 pl-3 pr-11 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 transition hover:text-gray-600">
                    <svg x-show="!show" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg x-show="show" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
            @error('password_confirmation')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <x-ui-button variant="primary" class="w-full" size="lg">Daftar</x-ui-button>
    </form>

    <p class="mt-8 border-t border-gray-200 pt-6 text-center text-sm text-gray-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-indigo-600 transition hover:text-indigo-500">Masuk</a>
    </p>
</x-guest-layout>
