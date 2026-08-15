<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jalur;
use App\Models\Promo;
use App\Models\PromoKetentuan;
use App\Models\ProdiKelasJalur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PromoController extends Controller
{
    public function index(): View
    {
        $promo = Promo::with('ketentuan.jalur', 'ketentuan.prodi', 'ketentuan.kelas')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.promo.index', compact('promo'));
    }
    public function create(): View
    {
        return view('admin.promo.form', [
            'promo' => new Promo(),
            'jalurList' => Jalur::orderBy('urutan')->get(),
            'matriksMap' => $this->matriksMap(),
            'ketentuanRows' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $promo = Promo::create($this->validated($request));
        $this->syncKetentuan($promo, $request);

        return redirect()->route('admin.promo.index')->with('success', 'Promo berhasil ditambahkan.');
    }

    public function edit(Promo $promo): View
    {
        $promo->load('ketentuan');

        $ketentuanRows = $promo->ketentuan->map(fn ($k) => [
            'id' => $k->id,
            'jalur_id' => $k->jalur_id,
            'prodi_id' => $k->prodi_id,
            'kelas_id' => $k->kelas_id,
        ])->values()->all();

        return view('admin.promo.form', [
            'promo' => $promo,
            'jalurList' => Jalur::orderBy('urutan')->get(),
            'matriksMap' => $this->matriksMap(),
            'ketentuanRows' => $ketentuanRows,
        ]);
    }

    public function update(Request $request, Promo $promo): RedirectResponse
    {
        $promo->update($this->validated($request, $promo->id));
        $this->syncKetentuan($promo, $request);

        return redirect()->route('admin.promo.index')->with('success', 'Promo berhasil diperbarui.');
    }

    public function destroy(Promo $promo): RedirectResponse
    {
        $promo->delete();

        return redirect()->route('admin.promo.index')->with('success', 'Promo berhasil dihapus.');
    }

    /**
     * Matriks valid (jalur → prodi → kelas) dari prodi_kelas_jalur untuk
     * keperluan dropdown berantai di form.
     */
    private function matriksMap(): array
    {
        $matriks = ProdiKelasJalur::with(['prodi', 'kelas'])->get()
            ->sortBy(fn ($r) => $r->prodi->jenjang.$r->prodi->nama);

        return $matriks->groupBy('jalur_id')->map(function ($rows) {
            return $rows->groupBy('prodi_id')->map(function ($pRows) {
                return [
                    'nama' => $pRows->first()->prodi->nama,
                    'jenjang' => $pRows->first()->prodi->jenjang,
                    'kelas' => $pRows->map(fn ($r) => [
                        'id' => $r->kelas_id,
                        'nama' => $r->kelas->nama,
                    ])->values()->all(),
                ];
            })->all();
        })->all();
    }

    /**
     * Simpan ketentuan promo (jalur + prodi + kelas) dengan delete + recreate.
     */
    private function syncKetentuan(Promo $promo, Request $request): void
    {
        $promo->ketentuan()->delete();

        // Promo global tidak membutuhkan ketentuan spesifik.
        if ($request->boolean('is_global')) {
            return;
        }

        foreach ($request->input('ketentuan', []) as $row) {
            if (empty($row['jalur_id']) || empty($row['prodi_id']) || empty($row['kelas_id'])) {
                continue;
            }

            PromoKetentuan::create([
                'promo_id' => $promo->id,
                'jalur_id' => $row['jalur_id'],
                'prodi_id' => $row['prodi_id'],
                'kelas_id' => $row['kelas_id'],
            ]);
        }
    }

    private function validated(Request $request, ?string $id = null): array
    {
        $data = $request->validate([
            'kode' => 'required|string|max:30|unique:promo,kode'.($id ? ','.$id : ''),
            'nama' => 'required|string|max:100',
            'jenis' => 'required|in:pendaftaran,spp,semua',
            'tipe' => 'required|in:persen,nominal',
            'nilai' => 'required|numeric|min:0',
            'maks_potongan' => 'nullable|numeric|min:0',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'ketentuan' => $request->boolean('is_global') ? 'nullable|array' : 'required|array|min:1',
            'ketentuan.*.jalur_id' => 'required|exists:jalur,id',
            'ketentuan.*.prodi_id' => 'required|exists:prodi,id',
            'ketentuan.*.kelas_id' => 'required|exists:kelas_perkuliahan,id',
        ]);

        // Untuk promo non-global, pastikan tiap kombinasi valid di prodi_kelas_jalur.
        if (! $request->boolean('is_global')) {
            foreach ($request->input('ketentuan', []) as $row) {
                $exists = ProdiKelasJalur::where('jalur_id', $row['jalur_id'])
                    ->where('prodi_id', $row['prodi_id'])
                    ->where('kelas_id', $row['kelas_id'])
                    ->exists();

                if (! $exists) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'ketentuan' => 'Terdapat kombinasi jalur, prodi, dan kelas yang tidak valid.',
                    ]);
                }
            }
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['is_global'] = $request->boolean('is_global');

        return $data;
    }
}
