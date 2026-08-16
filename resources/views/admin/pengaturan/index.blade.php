@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')
    <x-ui-page-header title="Pengaturan" description="Atur informasi umum aplikasi, termasuk SEO agar mudah ditemukan di mesin pencari." />

    <form method="POST" action="{{ route('admin.pengaturan.update') }}">
        @csrf
        @method('PUT')

        <x-ui-card>
            <div class="grid grid-cols-1 gap-6">
                @foreach ($fields as $key => [$label, $type])
                    <div>
                        <x-ui-label for="{{ $key }}">{{ $label }}</x-ui-label>
                        <div class="mt-2">
                            @if ($type === 'textarea')
                                <textarea name="{{ $key }}" id="{{ $key }}" rows="3"
                                          class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">{{ old($key, $values[$key]) }}</textarea>
                            @else
                                <x-ui-input name="{{ $key }}" id="{{ $key }}" :value="old($key, $values[$key])" />
                            @endif
                        </div>
                        @error($key)
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
            </div>
        </x-ui-card>
    </form>

    <x-ui-card class="mt-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Sinkronisasi Data NEO Feeder</h3>
                <p class="mt-1 text-xs text-gray-500">
                    Perbarui data referensi Agama dan Wilayah dari NEO Feeder ke database lokal.
                    Data ini dipakai pada formulir pendaftaran sehingga tidak perlu memanggil NEO Feeder langsung.
                </p>
            </div>
            <form method="POST" action="{{ route('admin.pengaturan.sync-neofeeder') }}" class="flex flex-wrap items-center gap-3">
                @csrf
                <label class="flex cursor-pointer select-none items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="fresh" value="1"
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                    Hapus wilayah lama sebelum sinkron
                </label>
                <x-ui-button variant="secondary" icon="adjust">Sinkronkan Sekarang</x-ui-button>
            </form>
        </div>
    </x-ui-card>
@endsection
