@extends('layouts.admin')

@section('title', 'Bank Soal CBT')

@section('content')
    <x-ui-page-header title="Bank Soal CBT" description="Kelola bank soal pilihan ganda untuk tes CBT.">
        <x-slot:action>
            <x-ui-button variant="primary" :href="route('admin.cbt-soal.create')" icon="plus">Tambah Soal</x-ui-button>
        </x-slot:action>
    </x-ui-page-header>

    <x-ui-card>
        <form method="GET" action="{{ route('admin.cbt-soal.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-5">
            <div>
                <x-ui-label for="jalur_id">Jalur</x-ui-label>
                <div class="mt-2">
                    <x-ui-select name="jalur_id" id="jalur_id">
                        <option value="">-- Semua Jalur --</option>
                        @foreach ($jalurList as $j)
                            <option value="{{ $j->id }}" @selected(request('jalur_id') == $j->id)>{{ $j->nama }}</option>
                        @endforeach
                    </x-ui-select>
                </div>
            </div>
            <div>
                <x-ui-label for="prodi_id">Program Studi</x-ui-label>
                <div class="mt-2">
                    <x-ui-select name="prodi_id" id="prodi_id">
                        <option value="">-- Semua Prodi --</option>
                        @foreach ($prodiList as $p)
                            <option value="{{ $p->id }}" @selected(request('prodi_id') == $p->id)>{{ $p->nama }}</option>
                        @endforeach
                    </x-ui-select>
                </div>
            </div>
            <div>
                <x-ui-label for="kategori">Kategori</x-ui-label>
                <div class="mt-2">
                    <x-ui-input name="kategori" id="kategori" list="kategori-suggest" :value="request('kategori')" placeholder="mis. Akademik" />
                    <datalist id="kategori-suggest">
                        @foreach ($kategoriList as $k)
                            <option value="{{ $k }}"></option>
                        @endforeach
                    </datalist>
                </div>
            </div>
            <div class="flex items-end gap-2 sm:col-span-2">
                <x-ui-button variant="primary" type="submit">Filter</x-ui-button>
                <x-ui-button variant="secondary" type="button" :href="route('admin.cbt-soal.index')">Reset</x-ui-button>
            </div>
        </form>
    </x-ui-card>

    <x-ui-card :padding="''" class="mt-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Pertanyaan</th>
                        <th class="px-6 py-3">Jalur</th>
                        <th class="px-6 py-3">Prodi</th>
                        <th class="px-6 py-3">Kategori</th>
                        <th class="px-6 py-3">Kunci</th>
                        <th class="px-6 py-3">Bobot</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($soal as $s)
                        <tr class="hover:bg-gray-50">
                            <td class="max-w-md px-6 py-3 font-medium text-gray-900">{{ \Illuminate\Support\Str::limit($s->pertanyaan, 90) }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $s->jalur?->nama ?? 'Umum' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $s->prodi?->nama ?? '—' }}</td>
                            <td class="px-6 py-3"><x-ui-badge color="blue">{{ $s->kategori }}</x-ui-badge></td>
                            <td class="px-6 py-3"><x-ui-badge color="indigo">{{ strtoupper($s->kunci_jawaban) }}</x-ui-badge></td>
                            <td class="px-6 py-3 text-gray-600">{{ $s->bobot }}</td>
                            <td class="px-6 py-3">
                                <x-ui-badge :color="$s->is_active ? 'green' : 'gray'">{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.cbt-soal.edit', $s) }}" class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.cbt-soal.destroy', $s) }}" onsubmit="return confirm('Hapus soal ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="8" message="Belum ada soal CBT." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($soal->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $soal->links() }}</div>
        @endif
    </x-ui-card>
@endsection
