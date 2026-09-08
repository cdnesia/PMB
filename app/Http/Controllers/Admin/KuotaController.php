<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jalur;
use App\Models\KelasPerkuliahan;
use App\Models\Kuota;
use App\Models\Prodi;
use App\Models\TahunPenerimaan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KuotaController extends Controller
{
    public function index(): View
    {
        $kuota = Kuota::with(['tahun', 'jalur', 'prodi', 'kelas'])
            ->latest()
            ->paginate(20);

        $tahunList = TahunPenerimaan::orderBy('kode')->get();
        $jalurList = Jalur::orderBy('urutan')->get();
        $prodiList = Prodi::orderBy('jenjang')->orderBy('nama')->get();
        $kelasList = KelasPerkuliahan::orderBy('nama')->get();

        return view('admin.kuota.index', compact('kuota', 'tahunList', 'jalurList', 'prodiList', 'kelasList'));
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

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->validated($request);
        $data['terpakai'] = 0;

        Kuota::create($data);

        return $this->ajaxSuccess($request, 'Kuota berhasil ditambahkan.', 'admin.kuota.index', status: 201);
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

    public function update(Request $request, Kuota $kuota): JsonResponse|RedirectResponse
    {
        $data = $this->validated($request);

        if ($data['jumlah'] < $kuota->terpakai) {
            throw ValidationException::withMessages([
                'jumlah' => 'Jumlah kuota tidak boleh kurang dari kuota terpakai ('.$kuota->terpakai.').',
            ]);
        }

        $kuota->update($data);

        return $this->ajaxSuccess($request, 'Kuota berhasil diperbarui.', 'admin.kuota.index');
    }

    public function destroy(Request $request, Kuota $kuota): JsonResponse|RedirectResponse
    {
        $kuota->delete();

        return $this->ajaxSuccess($request, 'Kuota berhasil dihapus.', 'admin.kuota.index');
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
