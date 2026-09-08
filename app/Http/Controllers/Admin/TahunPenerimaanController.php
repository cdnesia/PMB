<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunPenerimaan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TahunPenerimaanController extends Controller
{
    public function index(): View
    {
        $tahun = TahunPenerimaan::orderBy('kode')->paginate(20);

        return view('admin.tahun.index', compact('tahun'));
    }

    public function create(): View
    {
        return view('admin.tahun.form', ['tahun' => new TahunPenerimaan()]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        TahunPenerimaan::create($this->validated($request));

        return $this->ajaxSuccess($request, 'Tahun penerimaan berhasil ditambahkan.', 'admin.tahun.index', status: 201);
    }

    public function edit(TahunPenerimaan $tahun): View
    {
        return view('admin.tahun.form', compact('tahun'));
    }

    public function update(Request $request, TahunPenerimaan $tahun): JsonResponse|RedirectResponse
    {
        $tahun->update($this->validated($request, $tahun->id));

        return $this->ajaxSuccess($request, 'Tahun penerimaan berhasil diperbarui.', 'admin.tahun.index');
    }

    public function destroy(Request $request, TahunPenerimaan $tahun): JsonResponse|RedirectResponse
    {
        $tahun->delete();

        return $this->ajaxSuccess($request, 'Tahun penerimaan berhasil dihapus.', 'admin.tahun.index');
    }

    private function validated(Request $request, ?string $id = null): array
    {
        return $request->validate([
            'kode' => 'required|string|max:20|unique:tahun_penerimaan,kode'.($id ? ','.$id : ''),
            'nama' => 'required|string|max:100',
            'status' => 'required|in:draft,aktif,ditutup,arsip',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);
    }
}
