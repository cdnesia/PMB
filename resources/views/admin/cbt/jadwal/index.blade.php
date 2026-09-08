@extends('layouts.admin')

@section('title', 'Jadwal CBT')

@section('content')
    <div x-data="crudModal({
        storeUrl: @js(route('admin.cbt-jadwal.store')),
        defaults: {
            jalur_id: '', gelombang_id: '', prodi_id: '', nama: '', durasi_menit: 60,
            nilai_kelulusan_minimum: '', waktu_mulai: '', waktu_selesai: '',
            komposisi: [{ kategori: '', jumlah: 10, jumlah_prodi: 0 }], is_active: true,
        },
        addKomposisi() { this.form.komposisi.push({ kategori: '', jumlah: 10, jumlah_prodi: 0 }); },
        removeKomposisi(i) { if (this.form.komposisi.length > 1) this.form.komposisi.splice(i, 1); },
    })" x-effect="if (!form.prodi_id) form.komposisi.forEach(r => r.jumlah_prodi = 0)">
        <x-ui-page-header title="Jadwal CBT" description="Kelola jadwal pelaksanaan tes CBT per jalur.">
            <x-slot:action>
                <x-ui-button variant="primary" type="button" x-on:click="openCreate()" icon="plus">Tambah Jadwal</x-ui-button>
            </x-slot:action>
        </x-ui-page-header>

        <x-ui-card>
            <form method="GET" action="{{ route('admin.cbt-jadwal.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div class="sm:col-span-2">
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
                </div>

                <div class="flex items-center justify-end gap-2">
                    <x-ui-button variant="secondary" type="button" :href="route('admin.cbt-jadwal.index')">Reset</x-ui-button>
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
                                <th class="px-6 py-3">Jadwal</th>
                                <th class="px-6 py-3">Jalur</th>
                                <th class="px-6 py-3">Target Prodi</th>
                                <th class="px-6 py-3">Waktu Pelaksanaan</th>
                                <th class="px-6 py-3">Durasi</th>
                                <th class="px-6 py-3">Komposisi Soal</th>
                                <th class="px-6 py-3">Peserta</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($jadwal as $j)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $j->nama }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $j->jalur?->nama }}</td>
                                    <td class="px-6 py-3">
                                        @if ($j->prodi)
                                            <x-ui-badge color="indigo">{{ $j->prodi->nama }}</x-ui-badge>
                                        @else
                                            <span class="text-xs text-gray-400">Umum</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-gray-600">
                                        {{ $j->waktu_mulai?->format('d/m/Y H:i') }} — {{ $j->waktu_selesai?->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-600">{{ $j->durasi_menit }} menit</td>
                                    <td class="px-6 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($j->komposisi as $k)
                                                <x-ui-badge color="blue">{{ $k->kategori }}: {{ $k->jumlah }}{{ $k->jumlah_prodi > 0 ? '+'.$k->jumlah_prodi.' prodi' : '' }}</x-ui-badge>
                                            @endforeach
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ $j->totalSoalUmum() }} soal umum
                                            @if ($j->totalSoalProdiMaksimum() > 0)
                                                (+hingga {{ $j->totalSoalProdiMaksimum() }} khusus prodi)
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-3">
                                        <a href="{{ route('admin.cbt-jadwal.peserta', $j) }}" class="font-medium text-indigo-600 hover:underline">{{ $j->sesi_count }} peserta</a>
                                    </td>
                                    <td class="px-6 py-3">
                                        <x-ui-badge :color="$j->is_active ? 'green' : 'gray'">{{ $j->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui-badge>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button"
                                                x-on:click="openEdit(@js([
                                                    'id' => $j->id,
                                                    '_updateUrl' => route('admin.cbt-jadwal.update', $j),
                                                    'jalur_id' => $j->jalur_id,
                                                    'gelombang_id' => $j->gelombang_id,
                                                    'prodi_id' => $j->prodi_id,
                                                    'nama' => $j->nama,
                                                    'durasi_menit' => $j->durasi_menit,
                                                    'nilai_kelulusan_minimum' => $j->nilai_kelulusan_minimum,
                                                    'waktu_mulai' => $j->waktu_mulai?->format('Y-m-d\TH:i'),
                                                    'waktu_selesai' => $j->waktu_selesai?->format('Y-m-d\TH:i'),
                                                    'komposisi' => $j->komposisi->map(fn ($k) => ['kategori' => $k->kategori, 'jumlah' => $k->jumlah, 'jumlah_prodi' => $k->jumlah_prodi])->values()->all(),
                                                    'is_active' => (bool) $j->is_active,
                                                ]))"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </button>
                                            <button type="button"
                                                x-on:click="$dispatch('confirm-delete', { url: @js(route('admin.cbt-jadwal.destroy', $j)), message: @js('Hapus jadwal \''.$j->nama.'\'? Tindakan ini tidak bisa dibatalkan.') })"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-ui-empty-state :colspan="9" message="Belum ada jadwal CBT." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($jadwal->hasPages())
                    <div class="border-t border-gray-100 px-6 py-3">{{ $jadwal->links() }}</div>
                @endif
            </x-ui-card>
        </div>

        <x-crud-modal create-title="Tambah Jadwal CBT" edit-title="Edit Jadwal CBT" size="3xl">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-ui-label for="modal-jalur_id" required>Jalur</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="jalur_id" id="modal-jalur_id" x-model="form.jalur_id">
                            <option value="">-- Pilih Jalur --</option>
                            @foreach ($jalurList as $j)
                                <option value="{{ $j->id }}">{{ $j->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-gelombang_id">Gelombang</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="gelombang_id" id="modal-gelombang_id" x-model="form.gelombang_id">
                            <option value="">-- Semua Gelombang --</option>
                            @foreach ($gelombangList as $g)
                                <option value="{{ $g->id }}">{{ $g->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <x-ui-label for="modal-prodi_id">Program Studi Target</x-ui-label>
                    <div class="mt-2">
                        <x-ui-select name="prodi_id" id="modal-prodi_id" x-model="form.prodi_id">
                            <option value="">-- Umum (semua prodi di jalur ini) --</option>
                            @foreach ($prodiList as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">Kosongkan untuk jadwal umum. Pilih satu prodi untuk mengaktifkan kuota "Jml. Khusus Prodi" di komposisi.</p>
                </div>

                <div class="sm:col-span-2">
                    <x-ui-label for="modal-nama" required>Nama Jadwal</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input name="nama" id="modal-nama" x-model="form.nama" placeholder="Tes CBT Jalur Mandiri Gelombang 1" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-durasi_menit" required>Durasi (menit)</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" min="1" name="durasi_menit" id="modal-durasi_menit" x-model.number="form.durasi_menit" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-nilai_kelulusan_minimum">Nilai Kelulusan Minimum</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="number" step="0.01" min="0" max="100" name="nilai_kelulusan_minimum" id="modal-nilai_kelulusan_minimum" x-model="form.nilai_kelulusan_minimum" placeholder="Opsional, skala 0-100" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-waktu_mulai" required>Waktu Mulai</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="datetime-local" name="waktu_mulai" id="modal-waktu_mulai" x-model="form.waktu_mulai" />
                    </div>
                </div>

                <div>
                    <x-ui-label for="modal-waktu_selesai" required>Waktu Selesai</x-ui-label>
                    <div class="mt-2">
                        <x-ui-input type="datetime-local" name="waktu_selesai" id="modal-waktu_selesai" x-model="form.waktu_selesai" />
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <h3 class="text-sm font-semibold text-gray-900">Komposisi Soal</h3>
                <p class="mt-1 text-xs text-gray-500">Kolom "Jml. Khusus Prodi" hanya aktif jika Program Studi Target diisi.</p>

                @if ($kategoriList->isEmpty())
                    <div x-show="mode === 'create'" class="mt-3 flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        <x-icon name="warning" class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" />
                        <span>Belum ada kategori di Bank Soal. Tambah soal dulu dan isi kategorinya.</span>
                    </div>
                @endif

                <div class="mt-3 grid grid-cols-[1fr_6rem_6rem_2.5rem] gap-3 px-1 text-xs font-medium text-gray-500">
                    <span>Kategori</span>
                    <span>Jml. Umum</span>
                    <span>Khusus Prodi</span>
                    <span></span>
                </div>

                <div class="mt-1 space-y-2">
                    <template x-for="(row, index) in form.komposisi" :key="index">
                        <div class="grid grid-cols-[1fr_6rem_6rem_2.5rem] items-center gap-3">
                            <select x-model="row.kategori" x-select2
                                    class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($kategoriList as $k)
                                    <option value="{{ $k }}">{{ $k }}</option>
                                @endforeach
                            </select>
                            <input type="number" x-model.number="row.jumlah" min="1"
                                   class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <input type="number" x-model.number="row.jumlah_prodi" min="0"
                                   :disabled="!form.prodi_id" :placeholder="form.prodi_id ? '' : '—'"
                                   class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400">
                            <button type="button" x-on:click="removeKomposisi(index)"
                                    class="shrink-0 rounded-md p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600">
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>
                    </template>
                </div>

                <button type="button" x-on:click="addKomposisi()"
                        class="mt-3 inline-flex items-center gap-1.5 rounded-lg border border-dashed border-gray-300 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50">
                    <x-icon name="plus" class="h-3.5 w-3.5" /> Tambah Kategori
                </button>

                <p class="mt-2 text-xs text-gray-500"
                   x-text="'Total: ' + form.komposisi.reduce((sum, r) => sum + (parseInt(r.jumlah) || 0), 0) + ' soal umum, ditambah hingga ' + form.komposisi.reduce((sum, r) => sum + (parseInt(r.jumlah_prodi) || 0), 0) + ' soal khusus prodi.'"></p>
            </div>

            <x-ui-toggle name="is_active" id="modal-is_active" x-model="form.is_active" label="Aktif" description="Jadwal ini ditampilkan & bisa diakses peserta." />
        </x-crud-modal>
    </div>
@endsection
