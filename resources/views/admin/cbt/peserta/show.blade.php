@extends('layouts.admin')

@section('title', 'Detail Hasil CBT')

@section('content')
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.cbt-jadwal.peserta', $sesi->cbt_jadwal_id) }}" class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
            <x-icon name="arrow-left" class="h-5 w-5" />
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ $sesi->pendaftaran->user?->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $sesi->jadwal?->nama }} · {{ $sesi->pendaftaran->nomor_pendaftaran }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <x-ui-card>
                <h2 class="text-base font-semibold text-gray-900">Rincian Jawaban</h2>
                <div class="mt-4 divide-y divide-gray-100">
                    @foreach ($sesi->jawaban as $i => $j)
                        <div class="py-3">
                            <div class="flex items-start justify-between gap-4">
                                <p class="text-sm font-medium text-gray-900">{{ $i + 1 }}. {{ $j->soal?->pertanyaan }}</p>
                                @if ($j->jawaban === null)
                                    <x-ui-badge color="gray">Kosong</x-ui-badge>
                                @elseif ($j->is_benar)
                                    <x-ui-badge color="green">Benar</x-ui-badge>
                                @else
                                    <x-ui-badge color="red">Salah</x-ui-badge>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Jawaban peserta: <span class="font-medium text-gray-700">{{ $j->jawaban ? strtoupper($j->jawaban) : '—' }}</span>
                                · Kunci: <span class="font-medium text-gray-700">{{ strtoupper($j->soal?->kunci_jawaban) }}</span>
                                @if ($j->ragu_ragu) · <span class="text-amber-600">ditandai ragu-ragu</span> @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            </x-ui-card>

            <x-ui-card>
                <h2 class="text-base font-semibold text-gray-900">Log Pelanggaran</h2>
                <div class="mt-4 divide-y divide-gray-100">
                    @forelse ($sesi->pelanggaran as $p)
                        <div class="flex items-center justify-between py-2.5 text-sm">
                            <span class="text-gray-700">{{ str_replace('_', ' ', $p->jenis) }}{{ $p->keterangan ? ' — '.$p->keterangan : '' }}</span>
                            <span class="text-xs text-gray-400">{{ $p->terjadi_pada?->format('d/m/Y H:i:s') }}</span>
                        </div>
                    @empty
                        <p class="py-3 text-sm text-gray-400">Tidak ada pelanggaran tercatat.</p>
                    @endforelse
                </div>
            </x-ui-card>
        </div>

        <div class="space-y-6">
            <x-ui-card>
                <h2 class="text-base font-semibold text-gray-900">Ringkasan</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Skor</dt><dd class="font-semibold text-gray-900">{{ $sesi->skor !== null ? number_format($sesi->skor, 2) : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Benar</dt><dd class="text-emerald-600">{{ $sesi->jumlah_benar ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Salah</dt><dd class="text-red-600">{{ $sesi->jumlah_salah ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Kosong</dt><dd class="text-gray-500">{{ $sesi->jumlah_kosong ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Mulai</dt><dd class="text-gray-700">{{ $sesi->started_at?->format('d/m/Y H:i') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Selesai</dt><dd class="text-gray-700">{{ $sesi->finished_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Alasan Selesai</dt><dd class="text-gray-700">{{ $sesi->finish_reason ?? '—' }}</dd></div>
                </dl>
            </x-ui-card>

            @if ($sesi->status !== 'selesai')
                <x-ui-card>
                    <form method="POST" action="{{ route('admin.cbt-peserta.tutup', $sesi) }}" onsubmit="return confirm('Tutup & nilai sesi ini sekarang?')">
                        @csrf @method('PATCH')
                        <x-ui-button variant="danger" class="w-full justify-center">Tutup Paksa & Nilai</x-ui-button>
                    </form>
                </x-ui-card>
            @endif
        </div>
    </div>
@endsection
