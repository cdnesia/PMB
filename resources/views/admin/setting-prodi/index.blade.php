@extends('layouts.admin')

@section('title', 'Setting Prodi')

@section('content')
    <x-ui-page-header title="Setting Prodi" description="Atur kombinasi kelas perkuliahan & jalur yang tersedia di setiap prodi." />

    <x-ui-card :padding="''">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Program Studi</th>
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3">Jalur</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($prodi as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $p->jenjang ? $p->jenjang.' - ' : '' }}{{ $p->nama }}</td>
                            <td class="px-6 py-3 text-gray-600">
                                <x-ui-badge :color="$p->jumlah_kelas > 0 ? 'indigo' : 'gray'">{{ $p->jumlah_kelas }} kelas</x-ui-badge>
                            </td>
                            <td class="px-6 py-3 text-gray-600">
                                <x-ui-badge :color="$p->jumlah_jalur > 0 ? 'blue' : 'gray'">{{ $p->jumlah_jalur }} jalur</x-ui-badge>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <x-ui-button variant="secondary" size="sm" :href="route('admin.setting-prodi.edit', $p)" icon="adjust">Atur</x-ui-button>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="4" message="Belum ada program studi." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui-card>
@endsection
