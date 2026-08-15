@extends('layouts.admin')

@section('title', 'Setting Prodi: '.($prodi->jenjang ? $prodi->jenjang.' - ' : '').$prodi->nama)

@section('content')
    <x-ui-page-header title="Setting Prodi: {{ $prodi->jenjang ? $prodi->jenjang.' - ' : '' }}{{ $prodi->nama }}" description="Centang kombinasi kelas perkuliahan × jalur yang tersedia di prodi ini.">
        <x-slot:action>
            <x-ui-button variant="secondary" :href="route('admin.setting-prodi.index')" icon="arrow-left">Kembali</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <form method="POST" action="{{ route('admin.setting-prodi.update', $prodi) }}">
        @csrf
        @method('PUT')

        <x-ui-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Kelas \ Jalur</th>
                            @foreach ($jalurList as $j)
                                <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $j->nama }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($kelasList as $k)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $k->nama }}</td>
                                @foreach ($jalurList as $j)
                                    <td class="px-3 py-3 text-center">
                                        <label class="inline-flex cursor-pointer select-none items-center justify-center">
                                            <input type="checkbox"
                                                   name="combos[{{ $k->id }}|{{ $j->id }}]"
                                                   value="1"
                                                   @checked(isset($selected[$k->id.'|'.$j->id]))
                                                   class="peer sr-only">
                                            <span class="flex h-5 w-5 items-center justify-center rounded-md border border-gray-300 bg-white text-white transition-colors peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-600 peer-focus-visible:ring-offset-2">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                </svg>
                                            </span>
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
                <x-ui-button variant="primary" icon="check">Simpan Setting</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.setting-prodi.index')">Batal</x-ui-button>
            </div>
        </x-ui-card>
    </form>
@endsection
