@extends('layouts.mahasiswa')

@section('title', __('nav.cbt_test'))

@section('content')
    <x-ui-page-header :title="__('nav.cbt_test')" :description="__('cbt.index_description')" />

    <div class="space-y-6">
        @forelse ($data as $row)
            @php
                $p = $row['pendaftaran'];
                $jadwal = $row['jadwal'];
                $sesi = $row['sesi'];
            @endphp
            <x-ui-card>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">{{ $p->jalur?->namaLokal() }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $p->nomor_pendaftaran }}</p>
                    </div>

                    @if ($sesi && $sesi->sudahSelesai())
                        <x-ui-badge color="green">{{ __('cbt.exam_finished') }}</x-ui-badge>
                    @elseif ($sesi)
                        <x-ui-badge color="amber">{{ __('cbt.in_progress') }}</x-ui-badge>
                    @elseif ($jadwal)
                        <x-ui-badge color="blue">{{ __('cbt.schedule_available') }}</x-ui-badge>
                    @else
                        <x-ui-badge color="gray">{{ __('cbt.no_schedule_yet') }}</x-ui-badge>
                    @endif
                </div>

                <div class="mt-4 border-t border-gray-100 pt-4">
                    @if ($sesi && $sesi->sudahSelesai())
                        <div class="flex items-start gap-3 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
                            <div>
                                <span class="font-semibold">{{ __('cbt.exam_submitted') }}</span> {{ __('cbt.on_date', ['date' => $sesi->finished_at?->format('d/m/Y H:i')]) }}.
                                {{ __('cbt.result_with_selection_announcement') }}
                            </div>
                        </div>
                    @elseif ($sesi)
                        <div class="flex items-start gap-3 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            <x-icon name="warning" class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" />
                            <div class="flex-1">
                                <span class="font-semibold">{{ __('cbt.session_in_progress') }}</span>
                                {{ __('cbt.deadline_label') }}: {{ $sesi->deadline_at?->format('d/m/Y H:i') }}.
                            </div>
                            <x-ui-button variant="primary" size="sm" :href="route('mahasiswa.cbt.ujian', $sesi)">{{ __('cbt.continue_exam') }}</x-ui-button>
                        </div>
                    @elseif ($jadwal)
                        <div class="flex items-start gap-3 rounded-lg bg-sky-50 px-4 py-3 text-sm text-sky-800">
                            <x-icon name="info" class="mt-0.5 h-5 w-5 shrink-0 text-sky-500" />
                            <div class="flex-1">
                                <span class="font-semibold">{{ $jadwal->nama }}</span>
                                @if ($jadwal->prodi)
                                    <span class="ml-1"><x-ui-badge color="indigo">{{ $jadwal->prodi->nama }}</x-ui-badge></span>
                                @endif
                                <div class="mt-0.5 text-xs text-sky-700">
                                    {{ __('cbt.duration_minutes', ['n' => $jadwal->durasi_menit]) }} · {{ __('cbt.questions_count', ['n' => $jadwal->totalSoal()]) }} ·
                                    {{ __('cbt.window_until', ['date' => $jadwal->waktu_selesai?->format('d/m/Y H:i')]) }}
                                </div>
                            </div>
                            @if ($row['eligible'])
                                <form method="POST" action="{{ route('mahasiswa.cbt.mulai', $p) }}" onsubmit="return confirm('{{ __('cbt.confirm_start_exam') }}')">
                                    @csrf
                                    <x-ui-button variant="primary" size="sm">{{ __('cbt.start_exam') }}</x-ui-button>
                                </form>
                            @else
                                <x-ui-badge color="gray">{{ __('cbt.complete_payment_to_start') }}</x-ui-badge>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500">{{ __('cbt.no_active_schedule') }}</p>
                    @endif
                </div>
            </x-ui-card>
        @empty
            <x-ui-card>
                <p class="text-sm text-gray-500">{{ __('cbt.no_registration_requires_cbt') }}</p>
            </x-ui-card>
        @endforelse
    </div>
@endsection
