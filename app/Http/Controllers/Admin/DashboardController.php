<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jalur;
use App\Models\KelasPerkuliahan;
use App\Models\Kuota;
use App\Models\Pendaftaran;
use App\Models\Prodi;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'prodi' => Prodi::count(),
            'jalur' => Jalur::count(),
            'kelas' => KelasPerkuliahan::count(),
            'kuota' => Kuota::count(),
            'pendaftaran' => Pendaftaran::count(),
        ];

        // Ringkasan pendaftar yang perlu tindakan
        $pendaftarSummary = [
            'belum_bayar' => Pendaftaran::where('status_pembayaran', 'belum_bayar')->count(),
            'menunggu_verifikasi' => Pendaftaran::whereIn('status', ['lunas'])->count(),
            'terverifikasi' => Pendaftaran::where('status', 'terverifikasi')->count(),
            'lolos' => Pendaftaran::where('status', 'lolos')->count(),
        ];

        $kuota = Kuota::with(['prodi', 'jalur', 'kelas', 'tahun'])
            ->latest()
            ->take(10)
            ->get();

        $pendaftarTerbaru = Pendaftaran::with(['user', 'jalur'])
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'kuota', 'pendaftarSummary', 'pendaftarTerbaru'));
    }
}
