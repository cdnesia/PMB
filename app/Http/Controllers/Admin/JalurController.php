<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jalur;
use App\Models\KelasPerkuliahan;
use App\Models\SyaratJalur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JalurController extends Controller
{
    public function index(): View
    {
        $jalur = Jalur::with(['syarat', 'kelasBiaya.kelas'])->orderBy('urutan')->paginate(20);

        return view('admin.jalur.index', compact('jalur'));
    }

    public function create(): View
    {
        return view('admin.jalur.form', [
            'jalur' => new Jalur(),
            'syaratRows' => [],
            'kelasList' => KelasPerkuliahan::orderBy('nama')->get(),
            'biayaMap' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $jalur = Jalur::create($data);
        $this->syncSyarat($jalur, $request);
        $this->syncBiayaKelas($jalur, $request);

        return redirect()->route('admin.jalur.index')->with('success', 'Jalur berhasil ditambahkan.');
    }

    public function edit(Jalur $jalur): View
    {
        $jalur->load(['syarat', 'kelasBiaya']);

        $syaratRows = $jalur->syarat->map(fn ($s) => [
            'id' => $s->id,
            'tipe' => $s->tipe,
            'nama' => $s->nama,
            'kode' => $s->kode,
            'wajib' => (bool) $s->wajib,
        ])->values()->all();

        $biayaMap = $jalur->kelasBiaya
            ->mapWithKeys(fn ($b) => [$b->kelas_id => (float) $b->biaya_pendaftaran])
            ->all();

        return view('admin.jalur.form', [
            'jalur' => $jalur,
            'syaratRows' => $syaratRows,
            'kelasList' => KelasPerkuliahan::orderBy('nama')->get(),
            'biayaMap' => $biayaMap,
        ]);
    }

    public function update(Request $request, Jalur $jalur): RedirectResponse
    {
        $data = $this->validated($request, $jalur->id);

        $jalur->update($data);
        $this->syncSyarat($jalur, $request);
        $this->syncBiayaKelas($jalur, $request);

        return redirect()->route('admin.jalur.index')->with('success', 'Jalur berhasil diperbarui.');
    }

    public function destroy(Jalur $jalur): RedirectResponse
    {
        $jalur->delete();

        return redirect()->route('admin.jalur.index')->with('success', 'Jalur berhasil dihapus.');
    }

    /**
     * Simpan biaya pendaftaran per kelas untuk jalur ini (delete + recreate).
     */
    private function syncBiayaKelas(Jalur $jalur, Request $request): void
    {
        $jalur->kelasBiaya()->delete();

        foreach ($request->input('biaya_kelas', []) as $kelasId => $biaya) {
            if (! $kelasId || ! KelasPerkuliahan::whereKey($kelasId)->exists()) {
                continue;
            }

            $jalur->kelasBiaya()->create([
                'kelas_id' => $kelasId,
                'biaya_pendaftaran' => max(0, (float) ($biaya ?? 0)),
            ]);
        }
    }

    /**
     * Simpan syarat khusus jalur (field & file) dengan pendekatan delete + recreate.
     */
    private function syncSyarat(Jalur $jalur, Request $request): void
    {
        $jalur->syarat()->delete();

        foreach ($request->input('syarat', []) as $row) {
            if (empty(trim($row['nama'] ?? ''))) {
                continue;
            }

            $kode = trim($row['kode'] ?? '');
            if ($kode === '') {
                $kode = strtolower(str_replace(' ', '_', trim($row['nama'])));
            }

            SyaratJalur::create([
                'jalur_id' => $jalur->id,
                'tipe' => in_array($row['tipe'], ['field', 'file']) ? $row['tipe'] : 'field',
                'kode' => $kode,
                'nama' => trim($row['nama']),
                'wajib' => ! empty($row['wajib']),
                'is_active' => true,
            ]);
        }
    }

    private function validated(Request $request, ?string $id = null): array
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20|unique:jalur,kode'.($id ? ','.$id : ''),
            'nama' => 'required|string|max:100',
            'kategori' => 'required|in:nasional,mandiri',
            'urutan' => 'nullable|integer|min:0',
            'biaya_kelas' => 'nullable|array',
            'biaya_kelas.*' => 'nullable|numeric|min:0',
            'syarat' => 'nullable|array',
            'syarat.*.tipe' => 'nullable|in:field,file',
            'syarat.*.kode' => 'nullable|string|max:50',
            'syarat.*.nama' => 'nullable|string|max:150',
        ]);

        $data['urutan'] = $data['urutan'] ?? 0;
        $data['requires_cbt'] = $request->boolean('requires_cbt');
        $data['is_active'] = $request->boolean('is_active');

        unset($data['biaya_kelas']);

        return $data;
    }
}
