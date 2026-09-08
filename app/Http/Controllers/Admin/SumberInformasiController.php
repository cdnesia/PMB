<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SumberInformasi;
use Illuminate\Http\JsonResponse;
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

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->validated($request);

        SumberInformasi::create($data);

        return $this->ajaxSuccess($request, 'Sumber informasi berhasil ditambahkan.', 'admin.sumber-informasi.index', status: 201);
    }

    public function edit(SumberInformasi $sumberInformasi): View
    {
        return view('admin.sumber-informasi.form', ['sumber' => $sumberInformasi]);
    }

    public function update(Request $request, SumberInformasi $sumberInformasi): JsonResponse|RedirectResponse
    {
        $data = $this->validated($request, $sumberInformasi->id);

        $sumberInformasi->update($data);

        return $this->ajaxSuccess($request, 'Sumber informasi berhasil diperbarui.', 'admin.sumber-informasi.index');
    }

    public function destroy(Request $request, SumberInformasi $sumberInformasi): JsonResponse|RedirectResponse
    {
        $sumberInformasi->delete();

        return $this->ajaxSuccess($request, 'Sumber informasi berhasil dihapus.', 'admin.sumber-informasi.index');
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
