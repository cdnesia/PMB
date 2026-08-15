<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jalur;
use App\Models\Pendaftaran;
use App\Models\PendaftaranProdi;
use App\Models\Prodi;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(): View
    {
        // Ringkasan utama
        $ringkasan = [
            'total_pendaftar' => Pendaftaran::count(),
            'lunas' => Pendaftaran::where('status_pembayaran', 'lunas')->count(),
            'belum_bayar' => Pendaftaran::where('status_pembayaran', 'belum_bayar')->count(),
            'terverifikasi' => Pendaftaran::where('status', 'terverifikasi')->count(),
            'lolos' => Pendaftaran::where('status', 'lolos')->count(),
            'mahasiswa_baru' => Pendaftaran::where('status', 'mahasiswa_baru')->count(),
        ];

        // Rekap per jalur
        $perJalur = Jalur::withCount('kuota')
            ->orderBy('urutan')
            ->get()
            ->map(function ($j) {
                $pendaftar = Pendaftaran::where('jalur_id', $j->id);
                $j->pendaftar = (clone $pendaftar)->count();
                $j->lunas = (clone $pendaftar)->where('status_pembayaran', 'lunas')->count();
                $j->lolos = (clone $pendaftar)->where('status', 'lolos')->count();
                return $j;
            });

        // Rekap per prodi
        $perProdi = Prodi::orderBy('jenjang')->orderBy('nama')
            ->get()
            ->map(function ($p) {
                $pilihan = PendaftaranProdi::where('prodi_id', $p->id);
                $p->pendaftar = (clone $pilihan)->distinct('pendaftaran_id')->count('pendaftaran_id');
                $p->lolos = (clone $pilihan)->where('status', 'lolos')->count();
                return $p;
            });

        // Rekap per status
        $statusOrder = ['draft', 'menunggu_pembayaran', 'lunas', 'terverifikasi', 'lolos', 'cadangan', 'tidak_lolos', 'daftar_ulang', 'mahasiswa_baru', 'ditolak'];
        $perStatus = Pendaftaran::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusRekap = collect($statusOrder)->map(fn ($s) => [
            'status' => $s,
            'total' => $perStatus->get($s, 0),
        ])->values();

        return view('admin.laporan.index', compact('ringkasan', 'perJalur', 'perProdi', 'statusRekap'));
    }
}
