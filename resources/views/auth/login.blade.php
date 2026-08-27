<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('auth.welcome_title') }} 👋</h1>
        <p class="mt-2 text-sm leading-relaxed text-gray-500">{{ __('auth.login_subtitle') }}</p>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-ui-label for="email" required>{{ __('auth.email') }}</x-ui-label>
            <div class="mt-2">
                <x-ui-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
            </div>
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div x-data="{ show: false }">
            <div class="flex items-center justify-between">
                <x-ui-label for="password" required>{{ __('auth.password') }}</x-ui-label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 transition hover:text-indigo-500">
                        {{ __('auth.forgot_password') }}
                    </a>
                @endif
            </div>
            <div class="relative mt-2">
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="••••••••"
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

        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="flex cursor-pointer items-center gap-2.5">
                <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" name="remember">
                <span class="text-sm text-gray-600">{{ __('auth.remember_me') }}</span>
            </label>
        </div>

        <x-ui-button variant="primary" class="w-full" size="lg">{{ __('auth.login_button') }}</x-ui-button>
    </form>

    <p class="mt-8 border-t border-gray-200 pt-6 text-center text-sm text-gray-500">
        {{ __('auth.no_account') }}
        <a href="{{ route('register') }}" class="font-semibold text-indigo-600 transition hover:text-indigo-500">{{ __('auth.register_now') }}</a>
    </p>
</x-guest-layout>
