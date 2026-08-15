<x-guest-layout>
    <h1 class="text-xl font-bold text-gray-900">Reset Password</h1>
    <p class="mt-1 text-sm text-gray-500">Buat password baru untuk akun Anda.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-ui-label for="email" required>Email</x-ui-label>
            <div class="mt-2">
                <x-ui-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            </div>
            @error('email')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui-label for="password" required>Password Baru</x-ui-label>
            <div class="mt-2">
                <x-ui-input id="password" type="password" name="password" required autocomplete="new-password" />
            </div>
            @error('password')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui-label for="password_confirmation" required>Konfirmasi Password</x-ui-label>
            <div class="mt-2">
                <x-ui-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>
            @error('password_confirmation')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end">
            <x-ui-button variant="primary">Reset Password</x-ui-button>
        </div>
    </form>
</x-guest-layout>
