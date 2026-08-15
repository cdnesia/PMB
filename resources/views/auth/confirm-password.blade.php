<x-guest-layout>
    <h1 class="text-xl font-bold text-gray-900">Konfirmasi Password</h1>
    <p class="mt-1 text-sm text-gray-500">
        Ini adalah area aman. Harap konfirmasi password Anda sebelum melanjutkan.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <x-ui-label for="password" required>Password</x-ui-label>
            <div class="mt-2">
                <x-ui-input id="password" type="password" name="password" required autocomplete="current-password" autofocus />
            </div>
            @error('password')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end">
            <x-ui-button variant="primary">Konfirmasi</x-ui-button>
        </div>
    </form>
</x-guest-layout>
