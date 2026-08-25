<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\CbtSesi;
use App\Models\CbtSoal;
use App\Models\Pendaftaran;
use App\Services\CbtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CbtController extends Controller
{
    public function index(): View
    {
        $pendaftaran = Pendaftaran::where('user_id', Auth::id())
            ->with(['jalur', 'cbtSesi.jadwal'])
            ->whereHas('jalur', fn ($q) => $q->where('requires_cbt', true))
            ->get();

        $cbt = app(CbtService::class);

        $data = $pendaftaran->map(function (Pendaftaran $p) use ($cbt) {
            $sesi = $p->cbtSesi->first();
            $jadwal = $sesi?->jadwal ?? $cbt->jadwalBerlaku($p);

            $sudahBayar = ! in_array($p->status, ['draft', 'menunggu_pembayaran'], true);

            return [
                'pendaftaran' => $p,
                'jadwal' => $jadwal,
                'sesi' => $sesi,
                'eligible' => ! $sesi && $jadwal && $sudahBayar,
            ];
        });

        return view('mahasiswa.cbt.index', ['data' => $data]);
    }

    public function mulai(Pendaftaran $pendaftaran, CbtService $cbt): RedirectResponse
    {
        $this->authorizeMilik($pendaftaran);

        if (in_array($pendaftaran->status, ['draft', 'menunggu_pembayaran'], true)) {
            return back()->withErrors(['cbt' => 'Selesaikan pembayaran pendaftaran terlebih dahulu.']);
        }

        $jadwal = $cbt->jadwalBerlaku($pendaftaran);

        if (! $jadwal) {
            return back()->withErrors(['cbt' => 'Tidak ada jadwal CBT yang sedang berlaku untuk pendaftaran ini.']);
        }

        $sesi = $cbt->mulai($pendaftaran, $jadwal);

        return redirect()->route('mahasiswa.cbt.ujian', $sesi);
    }

    public function ujian(CbtSesi $sesi): View|RedirectResponse
    {
        $this->authorizeMilik($sesi->pendaftaran);

        if ($sesi->sudahLewatDeadline() && ! $sesi->sudahSelesai()) {
            app(CbtService::class)->finalisasi($sesi, 'auto_timeout');
        }

        if ($sesi->sudahSelesai()) {
            return redirect()->route('mahasiswa.cbt.index')->with('info', 'Ujian ini sudah selesai dikerjakan.');
        }

        $soalList = CbtSoal::whereIn('id', $sesi->soal_urutan)->get()->keyBy('id');
        $soalUrut = collect($sesi->soal_urutan)->map(fn ($id) => $soalList->get($id))->filter()->values();
        $jawabanTersimpan = $sesi->jawaban()->get()->keyBy('cbt_soal_id');

        return view('mahasiswa.cbt.ujian', compact('sesi', 'soalUrut', 'jawabanTersimpan'));
    }

    public function jawab(Request $request, CbtSesi $sesi): JsonResponse
    {
        $this->authorizeMilik($sesi->pendaftaran);

        $data = $request->validate([
            'cbt_soal_id' => 'required|exists:cbt_soal,id',
            'jawaban' => 'nullable|in:a,b,c,d,e',
            'ragu_ragu' => 'boolean',
        ]);

        $soal = CbtSoal::findOrFail($data['cbt_soal_id']);

        app(CbtService::class)->simpanJawaban($sesi, $soal, $data['jawaban'] ?? null, (bool) ($data['ragu_ragu'] ?? false));

        return response()->json(['ok' => true]);
    }

    public function pelanggaran(Request $request, CbtSesi $sesi): JsonResponse
    {
        $this->authorizeMilik($sesi->pendaftaran);

        $data = $request->validate([
            'jenis' => 'required|in:pindah_tab,keluar_fullscreen,copy_paste,klik_kanan,devtools',
            'keterangan' => 'nullable|string|max:255',
        ]);

        app(CbtService::class)->catatPelanggaran($sesi, $data['jenis'], $data['keterangan'] ?? null);

        return response()->json(['ok' => true]);
    }

    public function submit(CbtSesi $sesi, CbtService $cbt): RedirectResponse
    {
        $this->authorizeMilik($sesi->pendaftaran);

        $cbt->finalisasi($sesi, 'submit');

        return redirect()->route('mahasiswa.cbt.index')->with('success', 'Ujian berhasil dikumpulkan. Hasil akan diumumkan oleh panitia.');
    }

    private function authorizeMilik(Pendaftaran $pendaftaran): void
    {
        abort_unless($pendaftaran->user_id === Auth::id(), 403);
    }
}
