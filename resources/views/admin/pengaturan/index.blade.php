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
@endsection
