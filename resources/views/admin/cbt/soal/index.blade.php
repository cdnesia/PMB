@extends('layouts.admin')

@section('title', 'Bank Soal CBT')

@section('content')
    <div x-data="crudModal({
        storeUrl: @js(route('admin.cbt-soal.store')),
        defaults: {
            jalur_id: '', prodi_id: '', kategori: '', pertanyaan: '',
            pilihan_a: '', pilihan_b: '', pilihan_c: '', pilihan_d: '', pilihan_e: '',
            kunci_jawaban: 'a', bobot: 1, is_active: true,
        },
    })">
        <x-ui-page-header title="Bank Soal CBT" description="Kelola bank soal pilihan ganda untuk tes CBT.">
            <x-slot:action>
                <x-ui-button variant="primary" type="button" x-on:click="openCreate()" icon="plus">Tambah Soal</x-ui-button>
            </x-slot:action>
        </x-ui-page-header>

        <x-ui-card>
            <form method="GET" action="{{ route('admin.cbt-soal.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
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
                </div>

                <div class="flex items-center justify-end gap-2">
                    <x-ui-button variant="secondary" type="button" :href="route('admin.cbt-soal.index')">Reset</x-ui-button>
                    <x-ui-button variant="primary" type="submit">Filter</x-ui-button>
                </div>
            </form>
        </x-ui-card>

        <div id="crud-table" class="mt-6">
            <x-ui-card :padding="''">
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
                                            <button type="button"
                                                x-on:click="openEdit(@js([
                                                    'id' => $s->id,
                                                    '_updateUrl' => route('admin.cbt-soal.update', $s),
                                                    'jalur_id' => $s->jalur_id,
                                                    'prodi_id' => $s->prodi_id,
                                                    'kategori' => $s->kategori,
                                                    'pertanyaan' => $s->pertanyaan,
                                                    'pilihan_a' => $s->pilihan_a,
                                                    'pilihan_b' => $s->pilihan_b,
                                                    'pilihan_c' => $s->pilihan_c,
                                                    'pilihan_d' => $s->pilihan_d,
                                                    'pilihan_e' => $s->pilihan_e,
                                                    'kunci_jawaban' => $s->kunci_jawaban,
                                                    'bobot' => $s->bobot,
                                                    'is_active' => (bool) $s->is_active,
                                                ]))"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </button>
                                            <button type="button"
                                                x-on:click="$dispatch('confirm-delete', { url: @js(route('admin.cbt-soal.destroy', $s)), message: 'Hapus soal ini? Tindakan ini tidak bisa dibatalkan.' })"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
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
        </div>

        <x-crud-modal create-title="Tambah Soal CBT" edit-title="Edit Soal CBT" size="3xl">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <x-ui-label for="modal-jalur_id">Jalur</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="jalur_id" id="modal-jalur_id" x-model="form.jalur_id">
                            <option value="">-- Umum (lintas jalur) --</option>
                            @foreach ($jalurList as $j)
                                <option value="{{ $j->id }}">{{ $j->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-prodi_id">Program Studi</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="prodi_id" id="modal-prodi_id" x-model="form.prodi_id">
                            <option value="">-- Semua Prodi di Jalur ini --</option>
                            @foreach ($prodiList as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-kategori" required>Kategori</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="kategori" id="modal-kategori" list="kategori-suggest-modal" x-model="form.kategori" placeholder="mis. Akademik" />
                        <datalist id="kategori-suggest-modal">
                            @foreach ($kategoriList as $k)
                                <option value="{{ $k }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                </div>
            </div>

            <div>
                <x-ui-label for="modal-pertanyaan" required>Pertanyaan</x-ui-label>
                <div class="mt-2">
                    <textarea name="pertanyaan" id="modal-pertanyaan" rows="4" x-model="form.pertanyaan"
                        class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <h3 class="text-sm font-semibold text-gray-900">Pilihan Jawaban</h3>
                <p class="mt-1 text-xs text-gray-500">Pilihan A-D wajib diisi, pilihan E opsional. Tandai kunci jawaban di kolom kiri.</p>

                <div class="mt-3 space-y-3">
                    @foreach (['a', 'b', 'c', 'd', 'e'] as $huruf)
                        <div class="flex items-start gap-3">
                            <label class="mt-2.5 flex h-7 w-7 shrink-0 cursor-pointer items-center justify-center rounded-full border border-gray-300 text-xs font-semibold text-gray-500 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-600 has-[:checked]:text-white">
                                <input type="radio" name="kunci_jawaban" value="{{ $huruf }}" x-model="form.kunci_jawaban" class="sr-only">
                                {{ strtoupper($huruf) }}
                            </label>
                            <div class="flex-1">
                                <x-ui-input name="pilihan_{{ $huruf }}" id="modal-pilihan_{{ $huruf }}" x-model="form.pilihan_{{ $huruf }}" placeholder="Teks pilihan {{ strtoupper($huruf) }}{{ $huruf === 'e' ? ' (opsional)' : '' }}" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 border-t border-gray-100 pt-4 sm:grid-cols-2">
                <div>
                    <x-ui-label for="modal-bobot" required>Bobot Nilai</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" step="0.01" min="0.01" name="bobot" id="modal-bobot" x-model.number="form.bobot" />
                    </div>
                </div>
                <div class="flex items-end">
                    <x-ui-toggle name="is_active" id="modal-is_active" x-model="form.is_active" label="Aktif" description="Soal ikut diacak ke peserta saat ujian dimulai." />
                </div>
            </div>
        </x-crud-modal>
    </div>
@endsection
