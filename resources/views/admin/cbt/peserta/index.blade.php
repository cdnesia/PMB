@extends('layouts.admin')

@section('title', 'Peserta CBT — '.$jadwal->nama)

@section('content')
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.cbt-jadwal.index') }}" class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
            <x-icon name="arrow-left" class="h-5 w-5" />
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Peserta — {{ $jadwal->nama }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $jadwal->jalur?->nama }} · {{ $jadwal->waktu_mulai?->format('d/m/Y H:i') }} — {{ $jadwal->waktu_selesai?->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <x-ui-card :padding="''">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Peserta</th>
                        <th class="px-6 py-3">Mulai</th>
                        <th class="px-6 py-3">Selesai</th>
                        <th class="px-6 py-3">Skor</th>
                        <th class="px-6 py-3">Pelanggaran</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($sesi as $s)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <div class="font-medium text-gray-900">{{ $s->pendaftaran->user?->name }}</div>
                                <div class="text-xs text-gray-500">{{ $s->pendaftaran->nomor_pendaftaran }}</div>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $s->started_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $s->finished_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $s->skor !== null ? number_format($s->skor, 2) : '—' }}</td>
                            <td class="px-6 py-3">
                                @if ($s->jumlah_pelanggaran > 0)
                                    <x-ui-badge color="red">{{ $s->jumlah_pelanggaran }}x</x-ui-badge>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if ($s->status === 'selesai')
                                    <x-ui-badge color="green">Selesai ({{ $s->finish_reason }})</x-ui-badge>
                                @else
                                    <x-ui-badge color="amber">Berlangsung</x-ui-badge>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.cbt-peserta.show', $s) }}" class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Detail">
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>
                                    @if ($s->status !== 'selesai')
                                        <form method="POST" action="{{ route('admin.cbt-peserta.tutup', $s) }}" onsubmit="return confirm('Tutup & nilai sesi ini sekarang?')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Tutup Paksa">
                                                <x-icon name="warning" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-ui-empty-state :colspan="7" message="Belum ada peserta yang mengerjakan jadwal ini." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($sesi->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">{{ $sesi->links() }}</div>
        @endif
    </x-ui-card>
@endsection
