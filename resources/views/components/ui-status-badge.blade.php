@props(['status'])

@php
    $map = [
        'aktif' => ['Aktif', 'green'],
        'draft' => ['Draft', 'amber'],
        'ditutup' => ['Ditutup', 'gray'],
        'arsip' => ['Arsip', 'gray'],
        'lolos' => ['Lolos', 'green'],
        'cadangan' => ['Cadangan', 'amber'],
        'tidak_lolos' => ['Tidak Lolos', 'red'],
        'menunggu_pembayaran' => ['Menunggu Bayar', 'amber'],
        'lunas' => ['Lunas', 'green'],
        'terverifikasi' => ['Terverifikasi', 'blue'],
        'daftar_ulang' => ['Daftar Ulang', 'blue'],
        'mahasiswa_baru' => ['Mahasiswa Baru', 'green'],
        'ditolak' => ['Ditolak', 'red'],
        'menunggu' => ['Menunggu', 'amber'],
        'menunggu_verifikasi' => ['Menunggu Verifikasi', 'amber'],
        'belum_diunggah' => ['Belum Diunggah', 'gray'],
        'belum_bayar' => ['Belum Bayar', 'red'],
        'buka' => ['Buka', 'green'],
        'tutup' => ['Tutup', 'gray'],
    ];

    $key = $status ?? 'draft';
    [$label, $color] = $map[$key] ?? [str_replace('_', ' ', $key), 'gray'];
@endphp

<x-ui-badge :color="$color">{{ $label }}</x-ui-badge>
