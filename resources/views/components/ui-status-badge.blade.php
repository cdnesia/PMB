@props(['status'])

@php
    $map = [
        'aktif' => ['status.aktif', 'green'],
        'draft' => ['status.draft', 'amber'],
        'ditutup' => ['status.ditutup', 'gray'],
        'arsip' => ['status.arsip', 'gray'],
        'lolos' => ['status.lolos', 'green'],
        'cadangan' => ['status.cadangan', 'amber'],
        'tidak_lolos' => ['status.tidak_lolos', 'red'],
        'menunggu_pembayaran' => ['status.menunggu_pembayaran', 'amber'],
        'lunas' => ['status.lunas', 'green'],
        'terverifikasi' => ['status.terverifikasi', 'blue'],
        'daftar_ulang' => ['status.daftar_ulang', 'blue'],
        'mahasiswa_baru' => ['status.mahasiswa_baru', 'green'],
        'ditolak' => ['status.ditolak', 'red'],
        'menunggu' => ['status.menunggu', 'amber'],
        'menunggu_verifikasi' => ['status.menunggu_verifikasi', 'amber'],
        'belum_diunggah' => ['status.belum_diunggah', 'gray'],
        'belum_bayar' => ['status.belum_bayar', 'red'],
        'buka' => ['status.buka', 'green'],
        'tutup' => ['status.tutup', 'gray'],
    ];

    $key = $status ?? 'draft';
    [$labelKey, $color] = $map[$key] ?? [null, 'gray'];
    $label = $labelKey ? __($labelKey) : str_replace('_', ' ', $key);
@endphp

<x-ui-badge :color="$color">{{ $label }}</x-ui-badge>
