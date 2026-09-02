<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SumberInformasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SumberInformasiController extends Controller
{
    public function index(): View
    {
        $sumberInformasi = SumberInformasi::orderBy('urutan')->orderBy('nama')->paginate(20);

        return view('admin.sumber-informasi.index', compact('sumberInformasi'));
    }

    public function create(): View
    {
        return view('admin.sumber-informasi.form', ['sumber' => new SumberInformasi()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        SumberInformasi::create($data);

        return redirect()->route('admin.sumber-informasi.index')->with('success', 'Sumber informasi berhasil ditambahkan.');
    }

    public function edit(SumberInformasi $sumberInformasi): View
    {
        return view('admin.sumber-informasi.form', ['sumber' => $sumberInformasi]);
    }

    public function update(Request $request, SumberInformasi $sumberInformasi): RedirectResponse
    {
        $data = $this->validated($request, $sumberInformasi->id);

        $sumberInformasi->update($data);

        return redirect()->route('admin.sumber-informasi.index')->with('success', 'Sumber informasi berhasil diperbarui.');
    }

    public function destroy(SumberInformasi $sumberInformasi): RedirectResponse
    {
        $sumberInformasi->delete();

        return redirect()->route('admin.sumber-informasi.index')->with('success', 'Sumber informasi berhasil dihapus.');
    }

    private function validated(Request $request, ?string $id = null): array
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20|unique:sumber_informasi,kode'.($id ? ','.$id : ''),
            'nama' => 'required|string|max:100',
            'urutan' => 'nullable|integer|min:0',
        ]);

        $data['urutan'] = $data['urutan'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
