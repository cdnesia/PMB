<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pendaftaran = Pendaftaran::with(['tahun', 'gelombang', 'jalur', 'prodiPilihan.prodi', 'prodiPilihan.kelas'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('mahasiswa.dashboard', compact('pendaftaran'));
    }
}
