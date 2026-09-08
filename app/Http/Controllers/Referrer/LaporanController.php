<?php

namespace App\Http\Controllers\Referrer;

use App\Http\Controllers\Controller;
use App\Models\TahunPenerimaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $referrer = Auth::user()->referrerProfile()->firstOrFail();

        $tahunId = $request->input('tahun_id');

        $pendaftar = $referrer->pendaftaran()
            ->with('user')
            ->when($tahunId, fn ($q) => $q->where('tahun_id', $tahunId))
            ->latest()
            ->get();

        $tahunList = TahunPenerimaan::orderByDesc('kode')->get();

        return view('referrer.laporan', [
            'referrer' => $referrer,
            'pendaftar' => $pendaftar,
            'tahunList' => $tahunList,
            'tahunId' => $tahunId,
        ]);
    }
}
