<x-guest-layout>
    <h1 class="text-xl font-bold text-gray-900">{{ __('auth.forgot_password_title') }}</h1>
    <p class="mt-1 text-sm text-gray-500">
        {{ __('auth.forgot_password_subtitle') }}
    </p>

    @if (session('status'))
        <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <x-ui-label for="email" required>{{ __('auth.email') }}</x-ui-label>
            <div class="mt-2">
                <x-ui-input id="email" type="email" name="email" :value="old('email')" required autofocus placeholder="nama@email.com" />
            </div>
            @error('email')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end">
            <x-ui-button variant="primary">{{ __('auth.send_reset_link') }}</x-ui-button>
        </div>

        <p class="text-center text-sm text-gray-500">
            {{ __('auth.remember_password') }}
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ __('auth.login_link') }}</a>
        </p>
    </form>
</x-guest-layout>
