@extends('layouts.mahasiswa')

@section('title', 'Tes CBT')

@section('content')
    <x-ui-page-header title="Tes CBT" description="Status tes CBT untuk pendaftaran Anda." />

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
                        <h2 class="text-base font-semibold text-gray-900">{{ $p->jalur?->nama }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $p->nomor_pendaftaran }}</p>
                    </div>

                    @if ($sesi && $sesi->sudahSelesai())
                        <x-ui-badge color="green">Ujian Selesai</x-ui-badge>
                    @elseif ($sesi)
                        <x-ui-badge color="amber">Sedang Berlangsung</x-ui-badge>
                    @elseif ($jadwal)
                        <x-ui-badge color="blue">Jadwal Tersedia</x-ui-badge>
                    @else
                        <x-ui-badge color="gray">Belum Ada Jadwal</x-ui-badge>
                    @endif
                </div>

                <div class="mt-4 border-t border-gray-100 pt-4">
                    @if ($sesi && $sesi->sudahSelesai())
                        <div class="flex items-start gap-3 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
                            <div>
                                <span class="font-semibold">Ujian telah dikumpulkan</span> pada {{ $sesi->finished_at?->format('d/m/Y H:i') }}.
                                Hasil akan diumumkan oleh panitia bersama pengumuman seleksi.
                            </div>
                        </div>
                    @elseif ($sesi)
                        <div class="flex items-start gap-3 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            <x-icon name="warning" class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" />
                            <div class="flex-1">
                                <span class="font-semibold">Sesi ujian sedang berlangsung.</span>
                                Batas waktu: {{ $sesi->deadline_at?->format('d/m/Y H:i') }}.
                            </div>
                            <x-ui-button variant="primary" size="sm" :href="route('mahasiswa.cbt.ujian', $sesi)">Lanjutkan Ujian</x-ui-button>
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
                                    Durasi {{ $jadwal->durasi_menit }} menit · {{ $jadwal->totalSoal() }} soal ·
                                    Jendela waktu hingga {{ $jadwal->waktu_selesai?->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            @if ($row['eligible'])
                                <form method="POST" action="{{ route('mahasiswa.cbt.mulai', $p) }}" onsubmit="return confirm('Mulai ujian sekarang? Timer akan langsung berjalan dan tidak dapat dijeda.')">
                                    @csrf
                                    <x-ui-button variant="primary" size="sm">Mulai Ujian</x-ui-button>
                                </form>
                            @else
                                <x-ui-badge color="gray">Selesaikan pembayaran untuk memulai</x-ui-badge>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Belum ada jadwal CBT yang berlaku untuk jalur ini. Jadwal akan diinformasikan oleh panitia.</p>
                    @endif
                </div>
            </x-ui-card>
        @empty
            <x-ui-card>
                <p class="text-sm text-gray-500">Tidak ada pendaftaran Anda yang mewajibkan tes CBT.</p>
            </x-ui-card>
        @endforelse
    </div>
@endsection
