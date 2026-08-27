<section>
    <header class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('profile.password_title') }}</h2>
        <p class="mt-1 text-sm text-gray-500">{{ __('profile.password_subtitle') }}</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div>
            <x-ui-label for="update_password_current_password" required>{{ __('profile.current_password') }}</x-ui-label>
            <div class="mt-2">
                <x-ui-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" />
            </div>
            @error('current_password', 'updatePassword')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui-label for="update_password_password" required>{{ __('auth.new_password') }}</x-ui-label>
            <div class="mt-2">
                <x-ui-input id="update_password_password" name="password" type="password" autocomplete="new-password" />
            </div>
            @error('password', 'updatePassword')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui-label for="update_password_password_confirmation" required>{{ __('profile.confirm_new_password') }}</x-ui-label>
            <div class="mt-2">
                <x-ui-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
            </div>
            @error('password_confirmation', 'updatePassword')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4">
            <x-ui-button variant="primary" icon="check">{{ __('profile.save') }}</x-ui-button>
        </div>
    </form>
</section>
