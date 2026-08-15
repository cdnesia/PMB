<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunPenerimaan;
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

    public function store(Request $request): RedirectResponse
    {
        TahunPenerimaan::create($this->validated($request));

        return redirect()->route('admin.tahun.index')->with('success', 'Tahun penerimaan berhasil ditambahkan.');
    }

    public function edit(TahunPenerimaan $tahun): View
    {
        return view('admin.tahun.form', compact('tahun'));
    }

    public function update(Request $request, TahunPenerimaan $tahun): RedirectResponse
    {
        $tahun->update($this->validated($request, $tahun->id));

        return redirect()->route('admin.tahun.index')->with('success', 'Tahun penerimaan berhasil diperbarui.');
    }

    public function destroy(TahunPenerimaan $tahun): RedirectResponse
    {
        $tahun->delete();

        return redirect()->route('admin.tahun.index')->with('success', 'Tahun penerimaan berhasil dihapus.');
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
