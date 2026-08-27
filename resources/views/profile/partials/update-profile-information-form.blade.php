<section>
    <header class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('profile.info_title') }}</h2>
        <p class="mt-1 text-sm text-gray-500">{{ __('profile.info_subtitle') }}</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-ui-label for="name" required>{{ __('profile.name') }}</x-ui-label>
            <div class="mt-2">
                <x-ui-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            </div>
            @error('name')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui-label for="email" required>{{ __('auth.email') }}</x-ui-label>
            <div class="mt-2">
                <x-ui-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
            </div>
            @error('email')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <p>{{ __('profile.email_not_verified') }}</p>
                    <button form="send-verification" class="mt-1 font-medium text-amber-900 underline hover:text-amber-700">
                        {{ __('profile.resend_verification_link') }}
                    </button>
                </div>
            @endif
        </div>

        <div>
            <x-ui-label for="phone" required>{{ __('auth.phone') }}</x-ui-label>
            <div class="mt-2">
                <x-ui-input id="phone" name="phone" type="tel" :value="old('phone', $user->phone)" required autocomplete="tel" inputmode="numeric" placeholder="08xx xxxx xxxx" />
            </div>
            @error('phone')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4">
            <x-ui-button variant="primary" icon="check">{{ __('profile.save') }}</x-ui-button>
        </div>
    </form>
</section>
