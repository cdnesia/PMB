<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelasPerkuliahan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelasPerkuliahanController extends Controller
{
    public function index(): View
    {
        $kelas = KelasPerkuliahan::orderBy('nama')->paginate(20);

        return view('admin.kelas.index', compact('kelas'));
    }

    public function create(): View
    {
        return view('admin.kelas.form', ['kelas' => new KelasPerkuliahan()]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->validated($request);

        KelasPerkuliahan::create($data);

        return $this->ajaxSuccess($request, 'Kelas perkuliahan berhasil ditambahkan.', 'admin.kelas.index', status: 201);
    }

    public function edit(KelasPerkuliahan $kelas): View
    {
        return view('admin.kelas.form', compact('kelas'));
    }

    public function update(Request $request, KelasPerkuliahan $kelas): JsonResponse|RedirectResponse
    {
        $data = $this->validated($request, $kelas->id);

        $kelas->update($data);

        return $this->ajaxSuccess($request, 'Kelas perkuliahan berhasil diperbarui.', 'admin.kelas.index');
    }

    public function destroy(Request $request, KelasPerkuliahan $kelas): JsonResponse|RedirectResponse
    {
        $kelas->delete();

        return $this->ajaxSuccess($request, 'Kelas perkuliahan berhasil dihapus.', 'admin.kelas.index');
    }

    private function validated(Request $request, ?string $id = null): array
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20|unique:kelas_perkuliahan,kode'.($id ? ','.$id : ''),
            'nama' => 'required|string|max:100',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
