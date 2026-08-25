<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtJadwal;
use App\Models\CbtSesi;
use App\Services\CbtService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CbtPesertaController extends Controller
{
    public function index(CbtJadwal $jadwal): View
    {
        $sesi = $jadwal->sesi()
            ->with(['pendaftaran.user', 'pendaftaran.jalur'])
            ->latest('started_at')
            ->paginate(20);

        return view('admin.cbt.peserta.index', compact('jadwal', 'sesi'));
    }

    public function show(CbtSesi $sesi): View
    {
        $sesi->load([
            'jadwal',
            'pendaftaran.user',
            'jawaban.soal',
            'pelanggaran' => fn ($q) => $q->orderByDesc('terjadi_pada'),
        ]);

        return view('admin.cbt.peserta.show', compact('sesi'));
    }

    /**
     * Tutup paksa sesi yang macet (mis. peserta tidak bisa submit karena kendala teknis).
     */
    public function tutup(CbtSesi $sesi, CbtService $cbt): RedirectResponse
    {
        if ($sesi->sudahSelesai()) {
            return back()->with('info', 'Sesi ini sudah selesai.');
        }

        $cbt->finalisasi($sesi, 'admin_close');

        return back()->with('success', 'Sesi ujian ditutup & dinilai oleh panitia.');
    }
}
