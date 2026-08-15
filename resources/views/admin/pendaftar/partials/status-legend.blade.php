@php
    $legend = [
        ['status' => 'draft', 'desc' => 'Pendaftaran baru dibuat, formulir & dokumen sudah terkirim, belum diproses.'],
        ['status' => 'menunggu_pembayaran', 'desc' => 'Menunggu pembayaran biaya pendaftaran oleh calon mahasiswa.'],
        ['status' => 'lunas', 'desc' => 'Biaya pendaftaran lunas. Segera verifikasi kelengkapan berkas.'],
        ['status' => 'terverifikasi', 'desc' => 'Berkas lengkap & terverifikasi. Siap untuk dinilai/ikut seleksi.'],
        ['status' => 'lolos', 'desc' => 'Dinyatakan lolos seleksi. Menunggu pendaftar melakukan daftar ulang.'],
        ['status' => 'cadangan', 'desc' => 'Cadangan (waiting list). Dapat dipromosikan jika kuota belum penuh.'],
        ['status' => 'tidak_lolos', 'desc' => 'Tidak lolos seleksi pada pilihan prodi yang dipilih.'],
        ['status' => 'daftar_ulang', 'desc' => 'Pendaftar sudah mengirim bukti bayar SPP. Segera verifikasi pembayaran.'],
        ['status' => 'mahasiswa_baru', 'desc' => 'SPP lunas & daftar ulang selesai. Resmi menjadi mahasiswa baru.'],
        ['status' => 'ditolak', 'desc' => 'Pendaftaran ditolak (berkas/tidak memenuhi syarat).'],
    ];
@endphp

<div x-data="{ open: false }" class="rounded-xl border border-gray-200 bg-white">
    <button type="button" @click="open = !open"
            class="flex w-full items-center justify-between px-4 py-3 text-left">
        <span class="flex items-center gap-2 text-sm font-medium text-gray-700">
            <x-icon name="info" class="h-4 w-4 text-indigo-500" />
            Keterangan Status Pendaftaran
        </span>
        <svg class="h-4 w-4 text-gray-400 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div x-show="open" x-cloak class="border-t border-gray-100 px-4 py-4">
        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach ($legend as $item)
                <div class="flex items-start gap-2.5 rounded-lg bg-gray-50 px-3 py-2.5">
                    <div class="mt-0.5 shrink-0">
                        <x-ui-status-badge :status="$item['status']" />
                    </div>
                    <p class="text-xs leading-relaxed text-gray-600">{{ $item['desc'] }}</p>
                </div>
            @endforeach
        </dl>
    </div>
</div>
