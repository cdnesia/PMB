<x-guest-layout>
    <div class="text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50">
            <x-icon name="info" class="h-6 w-6 text-indigo-600" />
        </div>
        <h1 class="mt-4 text-lg font-bold text-gray-900">Verifikasi Email Anda</h1>
        <p class="mt-2 text-sm text-gray-600">
            Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-center text-sm text-emerald-700">
            Tautan verifikasi baru telah dikirim ke email Anda.
        </div>
    @endif

    <div class="mt-6 space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-ui-button variant="primary" class="w-full">Kirim Ulang Email Verifikasi</x-ui-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full rounded-lg py-2 text-center text-sm font-medium text-gray-600 transition hover:text-gray-900">
                Keluar
            </button>
        </form>
    </div>
</x-guest-layout>
