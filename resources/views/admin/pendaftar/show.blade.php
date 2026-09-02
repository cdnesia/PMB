@extends('layouts.admin')

@section('title', 'Detail Pendaftar')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.pendaftar.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-gray-900">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Daftar Pendaftar
        </a>
    </div>

    @include('admin.pendaftar.partials.status-legend')

    {{-- Header --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="bg-gradient-to-r from-slate-800 to-slate-700 px-6 py-6 text-white">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wider text-slate-300">Nomor Pendaftaran</div>
                    <div class="mt-1 font-mono text-xl font-bold">{{ $pendaftaran->nomor_pendaftaran }}</div>
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-300">
                        <x-icon name="calendar" class="h-3.5 w-3.5" />
                        Terdaftar {{ $pendaftaran->created_at->format('d M Y, H:i') }}
                    </div>
                </div>
                <div class="flex flex-wrap items-start gap-4">
                    <div class="text-right">
                        <div class="text-[11px] font-medium uppercase tracking-wider text-slate-300">Bayar Pendaftaran</div>
                        <div class="mt-1"><x-ui-status-badge :status="$pendaftaran->status_pembayaran" /></div>
                    </div>
                    <div class="text-right">
                        <div class="text-[11px] font-medium uppercase tracking-wider text-slate-300">Status Pendaftaran</div>
                        <div class="mt-1"><x-ui-status-badge :status="$pendaftaran->status" /></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
            {{-- Kolom kiri: data pendaftar & info --}}
            <div class="space-y-6 lg:col-span-2">
                {{-- Data pendaftar --}}
                <section>
                    <h3 class="text-sm font-semibold text-gray-900">Data Pendaftar</h3>
                    <dl class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-lg bg-gray-50 px-4 py-3">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Nama</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $pendaftaran->user?->name }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-4 py-3">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $pendaftaran->user?->email }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-4 py-3">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">No. WA / Telepon</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $pendaftaran->user?->phone ?? '—' }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-4 py-3">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Sumber Informasi</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $pendaftaran->user?->sumberInformasi?->nama ?? '—' }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-4 py-3">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Jalur</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ $pendaftaran->jalur?->nama }}
                                @if ($pendaftaran->jalur?->requires_cbt)
                                    <span class="ml-1"><x-ui-badge color="amber">CBT</x-ui-badge></span>
                                @endif
                            </dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-4 py-3">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Tahun Penerimaan</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $pendaftaran->tahun?->nama }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-4 py-3">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Biaya Pendaftaran</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                @if ($pendaftaran->biayaPendaftaranAwal() > 0)
                                    @if ($pendaftaran->potonganPendaftaran() > 0)
                                        <span class="text-gray-400 line-through">Rp {{ number_format($pendaftaran->biayaPendaftaranAwal(), 0, ',', '.') }}</span>
                                        <span class="text-emerald-600">Rp {{ number_format($pendaftaran->biayaPendaftaranAkhir(), 0, ',', '.') }}</span>
                                    @else
                                        Rp {{ number_format($pendaftaran->biayaPendaftaranAkhir(), 0, ',', '.') }}
                                    @endif
                                @else
                                    Gratis
                                @endif
                            </dd>
                            @if ($pendaftaran->promo)
                                <dd class="mt-1 text-xs text-emerald-700">
                                    Promo <span class="font-semibold">{{ $pendaftaran->promo->kode }}</span> — {{ $pendaftaran->promo->nama }} ({{ $pendaftaran->promo->labelPotongan() }})
                                </dd>
                            @endif
                        </div>
                    </dl>
                </section>

                {{-- Biodata Pendaftar (NEO Feeder) --}}
                @if ($pendaftaran->pendaftar)
                    @php $p = $pendaftaran->pendaftar; @endphp
                    <section class="mt-6">
                        <h3 class="text-sm font-semibold text-gray-900">Biodata Pendaftar</h3>
                        <dl class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="rounded-lg bg-gray-50 px-4 py-3">
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">NIK</dt>
                                <dd class="mt-1 font-mono text-sm text-gray-900">{{ $p->nik }}</dd>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-4 py-3">
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">NISN</dt>
                                <dd class="mt-1 font-mono text-sm text-gray-900">{{ $p->nisn ?? '—' }}</dd>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-4 py-3">
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Tempat, Tanggal Lahir</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $p->tempat_lahir }}, {{ $p->tanggal_lahir?->format('d/m/Y') }}</dd>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-4 py-3">
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Jenis Kelamin</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $p->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-4 py-3">
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Agama</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $p->agama ?? '—' }}@if ($p->agama_kode) <span class="font-mono text-xs text-gray-400">({{ $p->agama_kode }})</span>@endif</dd>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-4 py-3">
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Kewarganegaraan</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $p->kewarganegaraan ?? 'WNI' }}{{ $p->negara ? ' — '.$p->negara : '' }}@if ($p->negara_kode) <span class="font-mono text-xs text-gray-400">({{ $p->negara_kode }})</span>@endif</dd>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-4 py-3 sm:col-span-2">
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Alamat</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $p->alamat }}
                                    @if ($p->rt || $p->rw) <span class="text-gray-500">· RT {{ $p->rt ?? '-' }}/RW {{ $p->rw ?? '-' }}</span>@endif
                                    @if ($p->dusun) <span class="text-gray-500">· {{ $p->dusun }}</span>@endif
                                    @php
                                        $alamatWilayah = implode(', ', array_filter([
                                            $p->kelurahan, $p->kecamatan, $p->kota, $p->provinsi,
                                        ], fn ($v) => $v !== null && $v !== ''));
                                    @endphp
                                    @if ($alamatWilayah !== '')
                                        <span class="text-gray-500">· {{ $alamatWilayah }}</span>
                                    @endif
                                    @if ($p->kecamatan_kode)
                                        <span class="font-mono text-xs text-gray-400">(id: {{ $p->kecamatan_kode }})</span>
                                    @endif
                                    {{ $p->kode_pos ? ' '.$p->kode_pos : '' }}
                                </dd>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-4 py-3">
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Asal Sekolah</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $p->asal_sekolah }}@if ($p->tahun_lulus) <span class="text-gray-500">(lulus {{ $p->tahun_lulus }})</span>@endif</dd>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-4 py-3">
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Pekerjaan</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $p->pekerjaan ?? 'Belum bekerja / tidak diisi' }}
                                    @if ($p->tempat_bekerja)
                                        <span class="text-gray-500">— {{ $p->tempat_bekerja }}</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </section>
                @endif

                {{-- Pilihan prodi --}}
                <section>
                    <h3 class="text-sm font-semibold text-gray-900">Pilihan Prodi & Kelas</h3>
                    <div class="mt-3 space-y-3">
                        @forelse ($pendaftaran->prodiPilihan as $p)
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-xs font-bold text-indigo-600">{{ $p->urutan }}</span>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            @if ($p->prodi?->jenjang)
                                                <span class="text-indigo-600">{{ $p->prodi->jenjang }}</span>
                                                <span class="text-gray-300"> - </span>
                                            @endif
                                            {{ $p->prodi?->nama }}
                                        </div>
                                        <div class="text-xs text-gray-500">Kelas: {{ $p->kelas?->nama }}</div>
                                    </div>
                                </div>
                                @if ($p->status)
                                    <x-ui-status-badge :status="$p->status" />
                                @else
                                    <span class="text-xs text-gray-400">Belum dinilai</span>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400">
                                Belum ada pilihan prodi.
                            </div>
                        @endforelse
                    </div>
                </section>

                {{-- Dokumen --}}
                <section>
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Dokumen Persyaratan</h3>
                        @if ($pendaftaran->dokumen->isNotEmpty())
                            <form method="POST" action="{{ route('admin.pendaftar.verifikasi-berkas', $pendaftaran) }}">
                                @csrf
                                @method('PATCH')
                                <x-ui-button variant="success" size="sm" icon="check">Tandai Semua Berkas Lengkap</x-ui-button>
                            </form>
                        @endif
                    </div>
                    <div class="mt-3 space-y-3">
                        @forelse ($pendaftaran->dokumen as $d)
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500">
                                        <x-icon name="document" class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $d->nama }}</div>
                                        <div class="text-xs text-gray-500">
                                            @if ($d->file_name)
                                                {{ $d->file_name }}{{ $d->file_size ? ' · '.number_format($d->file_size / 1024, 0).' KB' : '' }}
                                            @else
                                                Belum diunggah
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if ($d->file_path)
                                        <a href="{{ asset('storage/'.$d->file_path) }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-sm font-medium text-indigo-600 transition hover:bg-indigo-50 hover:text-indigo-700">
                                            <x-icon name="eye" class="h-4 w-4" /> Lihat
                                        </a>
                                    @endif

                                    @if ($d->file_path)
                                        <form method="POST" action="{{ route('admin.pendaftar.dokumen-verifikasi', $d) }}" class="flex items-center gap-1">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" name="status" value="terverifikasi"
                                                    class="rounded-md p-1.5 text-gray-400 transition hover:bg-emerald-50 hover:text-emerald-600" title="Terima">
                                                <x-icon name="check" class="h-4 w-4" />
                                            </button>
                                            <button type="submit" name="status" value="ditolak"
                                                    class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Tolak">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @endif

                                    <x-ui-status-badge :status="$d->status" />
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400">
                                Tidak ada dokumen persyaratan.
                            </div>
                        @endforelse
                    </div>
                </section>

                {{-- Syarat Khusus Jalur --}}
                @if ($pendaftaran->syaratJawaban->isNotEmpty())
                    <section class="mt-6">
                        <h3 class="text-sm font-semibold text-gray-900">Syarat Khusus Jalur</h3>
                        <div class="mt-3 space-y-3">
                            @foreach ($pendaftaran->syaratJawaban as $j)
                                <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500">
                                            <x-icon name="{{ $j->syarat?->tipe === 'file' ? 'document' : 'credit-card' }}" class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $j->syarat?->nama }}</div>
                                            <div class="text-xs text-gray-500">
                                                @if ($j->syarat?->tipe === 'file')
                                                    @if ($j->file_name)
                                                        {{ $j->file_name }}{{ $j->file_size ? ' · '.number_format($j->file_size / 1024, 0).' KB' : '' }}
                                                    @else
                                                        Belum diunggah
                                                    @endif
                                                @else
                                                    {{ $j->nilai ?? '—' }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @if ($j->syarat?->tipe === 'file' && $j->file_path)
                                        <a href="{{ asset('storage/'.$j->file_path) }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-sm font-medium text-indigo-600 transition hover:bg-indigo-50 hover:text-indigo-700">
                                            <x-icon name="eye" class="h-4 w-4" /> Lihat
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            {{-- Kolom kanan: aksi proses --}}
            <div class="space-y-6">
                {{-- Daftar ulang (SPP) --}}
                @if ($pendaftaran->isLolos() && $pendaftaran->daftarUlang)
                    <section class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-5">
                        <h3 class="text-sm font-semibold text-emerald-900">Verifikasi SPP / Uang Kuliah (Daftar Ulang)</h3>
                        <p class="mt-1 text-xs text-gray-600">Pendaftar telah mengirim bukti pembayaran uang kuliah — terpisah dari biaya pendaftaran di bawah.</p>

                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Nominal</dt>
                                <dd class="font-medium text-gray-900">Rp {{ number_format($pendaftaran->daftarUlang->nominal, 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Tanggal Bayar</dt>
                                <dd class="font-medium text-gray-900">{{ $pendaftaran->daftarUlang->tanggal_bayar?->format('d/m/Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Status</dt>
                                <dd><x-ui-status-badge :status="$pendaftaran->daftarUlang->status" /></dd>
                            </div>
                        </dl>

                        @if ($pendaftaran->daftarUlang->bukti_bayar)
                            <a href="{{ asset('storage/'.$pendaftaran->daftarUlang->bukti_bayar) }}" target="_blank" rel="noopener"
                               class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                <x-icon name="eye" class="h-4 w-4" /> Lihat Bukti Bayar
                            </a>
                        @endif

                        @if ($pendaftaran->daftarUlang->status === 'menunggu_verifikasi')
                            <div class="mt-4 flex gap-2">
                                <form method="POST" action="{{ route('admin.pendaftar.daftar-ulang', $pendaftaran) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="lunas">
                                    <x-ui-button variant="success" class="w-full" icon="check">Konfirmasi Lunas</x-ui-button>
                                </form>
                                <form method="POST" action="{{ route('admin.pendaftar.daftar-ulang', $pendaftaran) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="ditolak">
                                    <x-ui-button variant="danger" class="w-full">Tolak</x-ui-button>
                                </form>
                            </div>
                        @endif
                    </section>
                @endif

                {{-- Pembayaran biaya pendaftaran (bukan SPP/uang kuliah) --}}
                <section class="rounded-xl border border-gray-200 p-5">
                    <h3 class="text-sm font-semibold text-gray-900">Validasi Biaya Pendaftaran</h3>
                    <p class="mt-1 text-xs text-gray-500">Konfirmasi pembayaran biaya formulir/pendaftaran — bukan SPP atau uang kuliah.</p>

                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Tagihan</dt>
                            <dd class="font-medium text-gray-900">
                                @if ($pendaftaran->biayaPendaftaranAwal() > 0)
                                    Rp {{ number_format($pendaftaran->biayaPendaftaranAkhir(), 0, ',', '.') }}
                                @else
                                    Gratis
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Status Saat Ini</dt>
                            <dd><x-ui-status-badge :status="$pendaftaran->status_pembayaran" /></dd>
                        </div>
                    </dl>

                    @if ($pendaftaran->pembayaran)
                        @php $bp = $pendaftaran->pembayaran; @endphp
                        <div class="mt-4 rounded-lg bg-gray-50 px-4 py-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Bukti Diunggah</span>
                                <x-ui-status-badge :status="$bp->status" />
                            </div>
                            <div class="mt-1 flex items-center justify-between">
                                <span class="text-gray-700">Rp {{ number_format($bp->nominal, 0, ',', '.') }} · {{ $bp->tanggal_bayar?->format('d/m/Y') }}</span>
                                @if ($bp->bukti_bayar)
                                    <a href="{{ asset('storage/'.$bp->bukti_bayar) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-700">
                                        <x-icon name="eye" class="h-3.5 w-3.5" /> Lihat Bukti
                                    </a>
                                @endif
                            </div>
                            @if ($bp->catatan)
                                <div class="mt-1 text-xs text-gray-500">Catatan: {{ $bp->catatan }}</div>
                            @endif
                        </div>

                        @if ($bp->status === 'menunggu_verifikasi')
                            <div class="mt-3 flex gap-2">
                                <form method="POST" action="{{ route('admin.pendaftar.verifikasi-pembayaran', $pendaftaran) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="lunas">
                                    <x-ui-button variant="success" class="w-full" icon="check">Konfirmasi Lunas</x-ui-button>
                                </form>
                                <form method="POST" action="{{ route('admin.pendaftar.verifikasi-pembayaran', $pendaftaran) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="ditolak">
                                    <x-ui-button variant="danger" class="w-full">Tolak</x-ui-button>
                                </form>
                            </div>
                        @endif
                    @endif

                    <div class="mt-4 flex gap-2">
                        <form method="POST" action="{{ route('admin.pendaftar.pembayaran', $pendaftaran) }}" class="flex-1">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status_pembayaran" value="lunas">
                            <x-ui-button variant="success" class="w-full" icon="check">Tandai Lunas</x-ui-button>
                        </form>
                        <form method="POST" action="{{ route('admin.pendaftar.pembayaran', $pendaftaran) }}" class="flex-1">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status_pembayaran" value="belum_bayar">
                            <x-ui-button variant="secondary" class="w-full">Belum Bayar</x-ui-button>
                        </form>
                    </div>
                </section>

                {{-- Nilai seleksi --}}
                <section class="rounded-xl border border-gray-200 p-5">
                    <h3 class="text-sm font-semibold text-gray-900">Nilai Seleksi</h3>
                    <p class="mt-1 text-xs text-gray-500">Masukkan skor akhir hasil seleksi.</p>

                    <form method="POST" action="{{ route('admin.pendaftar.nilai', $pendaftaran) }}" class="mt-4 space-y-3">
                        @csrf
                        @method('PATCH')
                        <div>
                            <x-ui-input type="number" name="nilai_seleksi" step="0.01" min="0" :value="old('nilai_seleksi', $pendaftaran->nilai_seleksi)" placeholder="0.00" />
                        </div>
                        <x-ui-button variant="primary" class="w-full" icon="check">Simpan Nilai</x-ui-button>
                    </form>
                </section>

                {{-- Hasil CBT --}}
                @if ($pendaftaran->jalur?->requires_cbt)
                    <section class="rounded-xl border border-gray-200 p-5">
                        <h3 class="text-sm font-semibold text-gray-900">Tes CBT</h3>
                        @php $cbtSesi = $pendaftaran->cbtSesi->first(); @endphp
                        @if (! $cbtSesi)
                            <p class="mt-1 text-xs text-gray-500">Pendaftar belum memulai tes CBT.</p>
                        @else
                            <div class="mt-3 space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Status</span>
                                    <x-ui-badge :color="$cbtSesi->status === 'selesai' ? 'green' : 'amber'">{{ $cbtSesi->status === 'selesai' ? 'Selesai' : 'Berlangsung' }}</x-ui-badge>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Skor</span>
                                    <span class="font-semibold text-gray-900">{{ $cbtSesi->skor !== null ? number_format($cbtSesi->skor, 2) : '—' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Pelanggaran</span>
                                    <span class="{{ $cbtSesi->jumlah_pelanggaran > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">{{ $cbtSesi->jumlah_pelanggaran }}x</span>
                                </div>
                            </div>
                            <a href="{{ route('admin.cbt-peserta.show', $cbtSesi) }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50">
                                <x-icon name="eye" class="h-3.5 w-3.5" /> Lihat Rincian Jawaban
                            </a>
                        @endif
                    </section>
                @endif

                {{-- Reset password --}}
                <section class="rounded-xl border border-gray-200 p-5">
                    <h3 class="text-sm font-semibold text-gray-900">Reset Password Pendaftar</h3>
                    <p class="mt-1 text-xs text-gray-500">Setel ulang kata sandi akun pendaftar.</p>

                    <form method="POST" action="{{ route('admin.pendaftar.reset-password', $pendaftaran) }}" class="mt-4 space-y-3">
                        @csrf
                        @method('PATCH')
                        <div>
                            <x-ui-label for="password" required>Password Baru</x-ui-label>
                            <div class="mt-2">
                                <x-ui-input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Min. 8 karakter" />
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-ui-label for="password_confirmation" required>Konfirmasi Password</x-ui-label>
                            <div class="mt-2">
                                <x-ui-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password" />
                            </div>
                        </div>
                        <x-ui-button variant="danger" class="w-full" icon="check">Reset Password</x-ui-button>
                    </form>
                </section>

                {{-- Kelulusan & status --}}
                <section class="rounded-xl border border-gray-200 p-5">
                    <h3 class="text-sm font-semibold text-gray-900">Kelulusan & Status</h3>
                    <p class="mt-1 text-xs text-gray-500">Tentukan status kelulusan per pilihan prodi.</p>

                    <form method="POST" action="{{ route('admin.pendaftar.status', $pendaftaran) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')

                        @foreach ($pendaftaran->prodiPilihan as $p)
                            <div>
                                <x-ui-label for="prodi_{{ $p->id }}">Pilihan {{ $p->urutan }} — {{ $p->prodi?->jenjang ? $p->prodi->jenjang.' - ' : '' }}{{ $p->prodi?->nama }}</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="prodi_status[{{ $p->id }}]" id="prodi_{{ $p->id }}">
                                        <option value="">-- Belum ditentukan --</option>
                                        @foreach (['lolos', 'cadangan', 'tidak_lolos'] as $s)
                                            <option value="{{ $s }}" @selected($p->status === $s)>{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                            </div>
                        @endforeach

                        <div>
                            <x-ui-label for="status" required>Status Pendaftaran</x-ui-label>
                            <div class="mt-2">
                                <x-ui-select name="status" id="status">
                                    @foreach (['draft', 'menunggu_pembayaran', 'lunas', 'terverifikasi', 'lolos', 'cadangan', 'tidak_lolos', 'daftar_ulang', 'mahasiswa_baru', 'ditolak'] as $s)
                                        <option value="{{ $s }}" @selected($pendaftaran->status === $s)>{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                                    @endforeach
                                </x-ui-select>
                            </div>
                        </div>

                        <x-ui-button variant="primary" class="w-full" icon="check">Simpan Status</x-ui-button>
                    </form>
                </section>
            </div>
        </div>
    </div>
@endsection
