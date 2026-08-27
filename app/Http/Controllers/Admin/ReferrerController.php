<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Models\Referrer;
use App\Models\TahunPenerimaan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferrerController extends Controller
{
    public function index(Request $request): View
    {
        $tahunId = $request->input('tahun_id');

        $referrer = Referrer::with('user')
            ->withCount([
                'pendaftaran' => fn ($q) => $q->when($tahunId, fn ($q) => $q->where('tahun_id', $tahunId)),
                'pendaftaran as lunas_count' => fn ($q) => $q->where('status_pembayaran', 'lunas')
                    ->when($tahunId, fn ($q) => $q->where('tahun_id', $tahunId)),
                'pendaftaran as lolos_count' => fn ($q) => $q->where('status', 'lolos')
                    ->when($tahunId, fn ($q) => $q->where('tahun_id', $tahunId)),
            ])
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis', $request->input('jenis')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($q) use ($search) {
                    $q->where('kode', 'like', "%{$search}%")
                        ->orWhere('nama_instansi', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('jenis')
            ->orderBy('kode')
            ->paginate(20)
            ->withQueryString();

        $ringkasan = [
            'total_referrer' => Referrer::count(),
            'aktif' => Referrer::where('is_active', true)->count(),
            'total_mahasiswa' => Pendaftaran::whereNotNull('referrer_id')
                ->when($tahunId, fn ($q) => $q->where('tahun_id', $tahunId))
                ->count(),
            'total_lunas' => Pendaftaran::whereNotNull('referrer_id')
                ->where('status_pembayaran', 'lunas')
                ->when($tahunId, fn ($q) => $q->where('tahun_id', $tahunId))
                ->count(),
        ];

        $tahunList = TahunPenerimaan::orderByDesc('kode')->get();

        return view('admin.referrer.index', compact('referrer', 'ringkasan', 'tahunList'));
    }

    public function show(Referrer $referrer): View
    {
        $referrer->load('user');

        $pendaftar = $referrer->pendaftaran()->with('user')->latest()->paginate(20)->withQueryString();

        return view('admin.referrer.show', compact('referrer', 'pendaftar'));
    }
}
