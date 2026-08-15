<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jalur;
use App\Models\KelasPerkuliahan;
use App\Models\Kuota;
use App\Models\Prodi;
use App\Models\TahunPenerimaan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KuotaController extends Controller
{
    public function index(): View
    {
        $kuota = Kuota::with(['tahun', 'jalur', 'prodi', 'kelas'])
            ->latest()
            ->paginate(20);

        return view('admin.kuota.index', compact('kuota'));
    }

    public function create(): View
    {
        return view('admin.kuota.form', [
            'kuota' => new Kuota(),
            'tahunList' => TahunPenerimaan::orderBy('kode')->get(),
            'jalurList' => Jalur::orderBy('urutan')->get(),
            'prodiList' => Prodi::orderBy('jenjang')->orderBy('nama')->get(),
            'kelasList' => KelasPerkuliahan::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['terpakai'] = 0;

        Kuota::create($data);

        return redirect()->route('admin.kuota.index')->with('success', 'Kuota berhasil ditambahkan.');
    }

    public function edit(Kuota $kuota): View
    {
        return view('admin.kuota.form', [
            'kuota' => $kuota,
            'tahunList' => TahunPenerimaan::orderBy('kode')->get(),
            'jalurList' => Jalur::orderBy('urutan')->get(),
            'prodiList' => Prodi::orderBy('jenjang')->orderBy('nama')->get(),
            'kelasList' => KelasPerkuliahan::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Kuota $kuota): RedirectResponse
    {
        $data = $this->validated($request);

        if ($data['jumlah'] < $kuota->terpakai) {
            return back()->withErrors(['jumlah' => 'Jumlah kuota tidak boleh kurang dari kuota terpakai ('.$kuota->terpakai.').']);
        }

        $kuota->update($data);

        return redirect()->route('admin.kuota.index')->with('success', 'Kuota berhasil diperbarui.');
    }

    public function destroy(Kuota $kuota): RedirectResponse
    {
        $kuota->delete();

        return redirect()->route('admin.kuota.index')->with('success', 'Kuota berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'tahun_id' => 'required|exists:tahun_penerimaan,id',
            'jalur_id' => 'required|exists:jalur,id',
            'prodi_id' => 'required|exists:prodi,id',
            'kelas_id' => 'nullable|exists:kelas_perkuliahan,id',
            'jumlah' => 'required|integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['kelas_id'] = $data['kelas_id'] ?: null;

        return $data;
    }
}
