@extends('layouts.mahasiswa')

@section('title', __('pendaftaran.show_title'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('mahasiswa.pendaftaran.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-gray-900">
            <x-icon name="arrow-left" class="h-4 w-4" /> {{ __('pendaftaran.back_to_my_registration') }}
        </a>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-6 py-6 text-white">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wider text-indigo-200">{{ __('pendaftaran.registration_number') }}</div>
                    <div class="mt-1 font-mono text-xl font-bold">{{ $pendaftaran->nomor_pendaftaran }}</div>
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-indigo-200">
                        <x-icon name="calendar" class="h-3.5 w-3.5" />
                        {{ __('pendaftaran.registered_on', ['date' => $pendaftaran->created_at->format('d M Y, H:i')]) }}
                    </div>
                </div>
                <x-ui-status-badge :status="$pendaftaran->status" />
            </div>
        </div>

        <div class="p-6">
            {{-- Progress timeline --}}
            @php
                $status = $pendaftaran->status;
                $steps = [
                    ['label' => __('pendaftaran.step_registration'), 'desc' => __('pendaftaran.step_registration_desc')],
                    ['label' => __('pendaftaran.step_verification'), 'desc' => __('pendaftaran.step_verification_desc')],
                    ['label' => __('pendaftaran.step_selection'), 'desc' => __('pendaftaran.step_selection_desc')],
                    ['label' => __('pendaftaran.step_reregistration'), 'desc' => __('pendaftaran.step_reregistration_desc')],
                    ['label' => __('pendaftaran.step_new_student'), 'desc' => __('pendaftaran.step_new_student_desc')],
                ];
                $current = match ($status) {
                    'terverifikasi' => 1,
                    'lolos', 'cadangan', 'tidak_lolos' => 2,
                    'daftar_ulang' => 3,
                    'mahasiswa_baru' => 4,
                    default => 0,
                };
                $isRejected = $status === 'ditolak';
            @endphp

            <section aria-label="{{ __('pendaftaran.progress_aria') }}">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('pendaftaran.registration_status') }}</h3>
                <ol class="mt-4">
                    @foreach ($steps as $i => $step)
                        @php
                            $done = ! $isRejected && $i < $current;
                            $active = ! $isRejected && $i === $current;
                        @endphp
                        <li class="relative flex gap-4 pb-6 last:pb-0">
                            @if (! $loop->last)
                                <span class="absolute left-[15px] top-8 h-full w-0.5 {{ $done || $active ? 'bg-indigo-200' : 'bg-gray-200' }}"></span>
                            @endif

                            <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full
                                {{ $done ? 'bg-indigo-600 text-white' : ($active ? 'bg-indigo-600 text-white ring-8 ring-indigo-50' : 'bg-gray-100 text-gray-400') }}">
                                @if ($done)
                                    <x-icon name="check" class="h-4 w-4" />
                                @elseif ($active)
                                    <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                                @else
                                    <span class="text-xs font-semibold">{{ $i + 1 }}</span>
                                @endif
                            </span>

                            <div class="pt-0.5">
                                <div class="text-sm font-semibold {{ $active ? 'text-gray-900' : ($done ? 'text-gray-700' : 'text-gray-400') }}">
                                    {{ $step['label'] }}
                                    @if ($active)
                                        <span class="ml-2 rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-medium text-indigo-600">{{ __('pendaftaran.current_step') }}</span>
                                    @endif
                                </div>
                                <div class="mt-0.5 text-xs text-gray-500">{{ $step['desc'] }}</div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            {{-- Pembayaran biaya pendaftaran --}}
            @if ($pendaftaran->status === 'menunggu_pembayaran')
                @php $bp = $pendaftaran->pembayaran; @endphp
                <div class="mt-6 overflow-hidden rounded-xl border border-indigo-200">
                    <div class="bg-indigo-50 px-5 py-4">
                        <div class="flex items-center gap-2">
                            <x-icon name="credit-card" class="h-5 w-5 text-indigo-600" />
                            <h3 class="text-sm font-semibold text-indigo-900">{{ __('pendaftaran.pay_registration_title') }}</h3>
                        </div>
                        <p class="mt-1 text-xs text-indigo-700">{{ __('pendaftaran.pay_registration_desc') }}</p>
                    </div>

                    <div class="bg-white p-5">
                        @if ($bp && $bp->status === 'menunggu_verifikasi')
                            <div class="flex items-start gap-3 rounded-lg bg-amber-50 px-4 py-3">
                                <x-icon name="info" class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" />
                                <div class="text-sm text-amber-800">
                                    <span class="font-semibold">{{ __('pendaftaran.proof_being_verified') }}</span>
                                    <div class="mt-1 text-xs text-amber-700">{{ __('pendaftaran.nominal') }}: Rp {{ number_format($bp->nominal, 0, ',', '.') }} · {{ $bp->file_name }}</div>
                                </div>
                            </div>
                        @elseif ($bp && $bp->status === 'ditolak')
                            <div class="mb-4 flex items-start gap-3 rounded-lg bg-red-50 px-4 py-3">
                                <x-icon name="warning" class="mt-0.5 h-5 w-5 shrink-0 text-red-500" />
                                <div class="text-sm text-red-800">
                                    <span class="font-semibold">{{ __('pendaftaran.proof_rejected') }}</span>
                                    {{ __('pendaftaran.reupload_correct_proof') }}
                                    @if ($bp->catatan)
                                        <div class="mt-1 text-xs text-red-700">{{ $bp->catatan }}</div>
                                    @endif
                                </div>
                            </div>
                            @include('mahasiswa.pendaftaran.partials.form-bayar-pendaftaran', ['pendaftaran' => $pendaftaran])
                        @else
                            @include('mahasiswa.pendaftaran.partials.form-bayar-pendaftaran', ['pendaftaran' => $pendaftaran])
                        @endif
                    </div>
                </div>
            @endif

            {{-- Daftar Ulang (jika lolos) --}}
            @if ($pendaftaran->isLolos())
                <div class="mt-6 overflow-hidden rounded-xl border border-emerald-200">
                    <div class="bg-emerald-50 px-5 py-4">
                        <div class="flex items-center gap-2">
                            <x-icon name="academic" class="h-5 w-5 text-emerald-600" />
                            <h3 class="text-sm font-semibold text-emerald-900">{{ __('pendaftaran.reregistration_title') }}</h3>
                        </div>
                        <p class="mt-1 text-xs text-emerald-700">{!! __('pendaftaran.reregistration_desc') !!}</p>
                    </div>

                    <div class="bg-white p-5">
                        @php $du = $pendaftaran->daftarUlang; @endphp

                        @if ($pendaftaran->status === 'mahasiswa_baru' && $du?->status === 'lunas')
                            <div class="flex items-start gap-3 rounded-lg bg-emerald-50 px-4 py-3">
                                <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
                                <div class="text-sm text-emerald-800">
                                    <span class="font-semibold">{{ __('pendaftaran.payment_verified') }}</span>
                                    {!! __('pendaftaran.now_new_student') !!}
                                </div>
                            </div>
                        @elseif ($du && $du->status === 'menunggu_verifikasi')
                            <div class="flex items-start gap-3 rounded-lg bg-amber-50 px-4 py-3">
                                <x-icon name="info" class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" />
                                <div class="text-sm text-amber-800">
                                    <span class="font-semibold">{{ __('pendaftaran.proof_being_verified') }}</span>
                                    <div class="mt-1 text-xs text-amber-700">{{ __('pendaftaran.nominal') }}: Rp {{ number_format($du->nominal, 0, ',', '.') }} · {{ $du->file_name }}</div>
                                </div>
                            </div>
                        @elseif ($du && $du->status === 'ditolak')
                            <div class="mb-4 flex items-start gap-3 rounded-lg bg-red-50 px-4 py-3">
                                <x-icon name="warning" class="mt-0.5 h-5 w-5 shrink-0 text-red-500" />
                                <div class="text-sm text-red-800">
                                    <span class="font-semibold">{{ __('pendaftaran.proof_rejected') }}</span>
                                    {{ __('pendaftaran.reupload_correct_proof') }}
                                </div>
                            </div>
                            @include('mahasiswa.pendaftaran.partials.form-daftar-ulang', ['pendaftaran' => $pendaftaran])
                        @else
                            @include('mahasiswa.pendaftaran.partials.form-daftar-ulang', ['pendaftaran' => $pendaftaran])
                        @endif
                    </div>
                </div>
            @endif

            {{-- Banner CBT (jika jalur mewajibkan) --}}
            @if ($pendaftaran->jalur?->requires_cbt)
                @php $cbtSesi = $pendaftaran->cbtSesi->first(); @endphp
                <div class="mt-6 flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                    <x-icon name="warning" class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" />
                    <div class="flex-1 text-sm text-amber-800">
                        @if ($cbtSesi?->sudahSelesai())
                            <span class="font-semibold">{{ __('pendaftaran.cbt_submitted') }}</span>
                            <span class="text-amber-700"> {{ __('pendaftaran.cbt_result_announced') }}</span>
                        @elseif ($cbtSesi)
                            <span class="font-semibold">{{ __('pendaftaran.cbt_in_progress') }}</span>
                            <span class="text-amber-700"> {{ __('pendaftaran.cbt_deadline', ['date' => $cbtSesi->deadline_at->format('d/m/Y H:i')]) }}</span>
                        @else
                            <span class="font-semibold">{{ __('pendaftaran.cbt_required') }}</span>
                            <span class="text-amber-700"> {{ __('pendaftaran.cbt_check_schedule') }}</span>
                        @endif
                    </div>
                    <x-ui-button variant="secondary" size="sm" :href="route('mahasiswa.cbt.index')">{{ __('pendaftaran.open_cbt') }}</x-ui-button>
                </div>
            @endif

            {{-- Info ringkas --}}
            <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-gray-50 px-4 py-3">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('pendaftaran.admission_year') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $pendaftaran->tahun?->nama }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 px-4 py-3">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('pendaftaran.wave') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $pendaftaran->gelombang?->nama ?? '—' }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 px-4 py-3">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('pendaftaran.pathway') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $pendaftaran->jalur?->nama }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 px-4 py-3">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('pendaftaran.registration_fee') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">
                        @if ($pendaftaran->biayaPendaftaranAwal() > 0)
                            @if ($pendaftaran->potonganPendaftaran() > 0)
                                <span class="text-gray-400 line-through">Rp {{ number_format($pendaftaran->biayaPendaftaranAwal(), 0, ',', '.') }}</span>
                                <span class="text-emerald-600">Rp {{ number_format($pendaftaran->biayaPendaftaranAkhir(), 0, ',', '.') }}</span>
                            @else
                                Rp {{ number_format($pendaftaran->biayaPendaftaranAkhir(), 0, ',', '.') }}
                            @endif
                        @else
                            {{ __('pendaftaran.free') }}
                        @endif
                    </dd>
                    @if ($pendaftaran->promo)
                        <dd class="mt-1 text-xs text-emerald-700">
                            {{ __('pendaftaran.promo') }} <span class="font-semibold">{{ $pendaftaran->promo->kode }}</span> — {{ __('pendaftaran.discount') }} {{ $pendaftaran->promo->labelPotongan() }}
                        </dd>
                    @endif
                </div>
            </dl>

            @if ($pendaftaran->pendaftar)
                @php $p = $pendaftaran->pendaftar; @endphp
                <h3 class="mt-8 text-sm font-semibold text-gray-900">{{ __('pendaftaran.personal_data') }}</h3>
                <dl class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-lg bg-gray-50 px-4 py-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('pendaftaran.nik') }}</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900">{{ $p->nik }}</dd>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-4 py-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('pendaftaran.nisn') }}</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900">{{ $p->nisn ?? '—' }}</dd>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-4 py-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('pendaftaran.birth_place_date') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $p->tempat_lahir }}, {{ $p->tanggal_lahir?->format('d/m/Y') }}</dd>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-4 py-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('pendaftaran.gender') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $p->jenis_kelamin === 'L' ? __('pendaftaran.male') : __('pendaftaran.female') }}</dd>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-4 py-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('pendaftaran.religion') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $p->agama ?? '—' }}@if ($p->agama_kode) <span class="font-mono text-xs text-gray-400">({{ $p->agama_kode }})</span>@endif</dd>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-4 py-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('pendaftaran.nationality') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $p->kewarganegaraan ?? 'WNI' }}{{ $p->negara ? ' — '.$p->negara : '' }}@if ($p->negara_kode) <span class="font-mono text-xs text-gray-400">({{ $p->negara_kode }})</span>@endif</dd>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-4 py-3 sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('pendaftaran.address') }}</dt>
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
                            {{ $p->kode_pos ? ' '.$p->kode_pos : '' }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-4 py-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('pendaftaran.school_origin') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $p->asal_sekolah }}@if ($p->tahun_lulus) <span class="text-gray-500">({{ __('pendaftaran.graduated_in', ['year' => $p->tahun_lulus]) }})</span>@endif</dd>
                    </div>
                    @if ($p->pekerjaan)
                        <div class="rounded-lg bg-gray-50 px-4 py-3">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('pendaftaran.occupation') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $p->pekerjaan }}
                                @if ($p->tempat_bekerja)
                                    <span class="text-gray-500">— {{ $p->tempat_bekerja }}</span>
                                @endif
                            </dd>
                        </div>
                    @endif
                </dl>
            @endif

            <h3 class="mt-8 text-sm font-semibold text-gray-900">{{ __('pendaftaran.program_choices') }}</h3>
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
                                <div class="text-xs text-gray-500">{{ __('pendaftaran.class') }}: {{ $p->kelas?->nama }}</div>
                            </div>
                        </div>
                        @if ($p->status)
                            <x-ui-status-badge :status="$p->status" />
                        @endif
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400">
                        {{ __('pendaftaran.no_program_choice') }}
                    </div>
                @endforelse
            </div>

            <h3 class="mt-8 text-sm font-semibold text-gray-900">{{ __('pendaftaran.required_documents') }}</h3>
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
                                        {{ __('pendaftaran.not_uploaded') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if ($d->file_path)
                                <a href="{{ asset('storage/'.$d->file_path) }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-sm font-medium text-indigo-600 transition hover:bg-indigo-50 hover:text-indigo-700">
                                    <x-icon name="eye" class="h-4 w-4" />
                                    {{ __('pendaftaran.view') }}
                                    <x-icon name="external-link" class="h-3 w-3 text-indigo-400" />
                                </a>
                            @endif
                            <x-ui-status-badge :status="$d->status" />
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400">
                        {{ __('pendaftaran.no_required_documents') }}
                    </div>
                @endforelse
            </div>

            @if ($pendaftaran->syaratJawaban->isNotEmpty())
                <h3 class="mt-8 text-sm font-semibold text-gray-900">{{ __('pendaftaran.pathway_specific_requirements') }}</h3>
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
                                                {{ __('pendaftaran.not_uploaded') }}
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
                                    <x-icon name="eye" class="h-4 w-4" />
                                    {{ __('pendaftaran.view') }}
                                    <x-icon name="external-link" class="h-3 w-3 text-indigo-400" />
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
