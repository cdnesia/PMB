<section>
    <header class="mb-6">
        <h2 class="text-lg font-semibold text-red-600">{{ __('profile.delete_account_title') }}</h2>
        <p class="mt-1 text-sm text-gray-500">
            {{ __('profile.delete_account_warning') }}
        </p>
    </header>

    <x-ui-button
        variant="danger"
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        <x-icon name="trash" class="h-4 w-4" /> {{ __('profile.delete_account_title') }}
    </x-ui-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-gray-900">
                {{ __('profile.delete_confirm_title') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('profile.delete_confirm_body') }}
            </p>

            <div class="mt-6">
                <x-ui-label for="password" required>{{ __('auth.password') }}</x-ui-label>
                <div class="mt-2">
                    <x-ui-input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="{{ __('auth.password') }}"
                    />
                </div>
                @error('password', 'userDeletion')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-ui-button variant="secondary" type="button" x-on:click="$dispatch('close')">{{ __('cbt.cancel') }}</x-ui-button>
                <x-ui-button variant="danger">{{ __('profile.delete_account_title') }}</x-ui-button>
            </div>
        </form>
    </x-modal>
</section>
