<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DokumenPersyaratan;
use App\Models\Jalur;
use App\Models\Prodi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DokumenPersyaratanController extends Controller
{
    public function index(Request $request): View
    {
        $dokumen = DokumenPersyaratan::with(['jalur', 'prodi'])
            ->when($request->filled('jalur_id'), fn ($q) => $q->where('jalur_id', $request->jalur_id))
            ->when($request->filled('prodi_id'), fn ($q) => $q->where('prodi_id', $request->prodi_id))
            ->when($request->filled('scope') && $request->scope === 'jalur', fn ($q) => $q->whereNotNull('jalur_id'))
            ->when($request->filled('scope') && $request->scope === 'prodi', fn ($q) => $q->whereNotNull('prodi_id'))
            ->orderBy('jalur_id')
            ->orderBy('prodi_id')
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        $jalurList = Jalur::orderBy('urutan')->get();
        $prodiList = Prodi::orderBy('jenjang')->orderBy('nama')->get();

        return view('admin.dokumen.index', compact('dokumen', 'jalurList', 'prodiList'));
    }

    public function create(Request $request): View
    {
        $jalurList = Jalur::orderBy('urutan')->get();
        $prodiList = Prodi::orderBy('jenjang')->orderBy('nama')->get();

        return view('admin.dokumen.create', compact('jalurList', 'prodiList'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'jalur_id' => 'nullable|exists:jalur,id',
            'prodi_id' => 'nullable|exists:prodi,id',
            'dokumen' => 'required|array|min:1',
            'dokumen.*.nama' => 'required|string|max:200',
        ]);

        if (! $request->filled('jalur_id') && ! $request->filled('prodi_id')) {
            throw ValidationException::withMessages(['jalur_id' => 'Pilih minimal satu: jalur atau prodi.']);
        }

        $jalurId = $request->filled('jalur_id') ? $request->jalur_id : null;
        $prodiId = $request->filled('prodi_id') ? $request->prodi_id : null;

        foreach ($request->input('dokumen', []) as $row) {
            if (empty(trim($row['nama']))) {
                continue;
            }

            DokumenPersyaratan::create([
                'jalur_id' => $jalurId,
                'prodi_id' => $prodiId,
                'nama' => trim($row['nama']),
                'wajib' => ! empty($row['wajib']),
                'is_active' => true,
            ]);
        }

        return $this->ajaxSuccess($request, 'Dokumen persyaratan berhasil ditambahkan.', 'admin.dokumen.index', status: 201);
    }

    public function edit(DokumenPersyaratan $dokumen): View
    {
        $jalurList = Jalur::orderBy('urutan')->get();
        $prodiList = Prodi::orderBy('jenjang')->orderBy('nama')->get();

        return view('admin.dokumen.edit', compact('dokumen', 'jalurList', 'prodiList'));
    }

    public function update(Request $request, DokumenPersyaratan $dokumen): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'jalur_id' => 'nullable|exists:jalur,id',
            'prodi_id' => 'nullable|exists:prodi,id',
            'nama' => 'required|string|max:200',
        ]);

        if (! $request->filled('jalur_id') && ! $request->filled('prodi_id')) {
            throw ValidationException::withMessages(['jalur_id' => 'Pilih minimal satu: jalur atau prodi.']);
        }

        $data['jalur_id'] = $request->filled('jalur_id') ? $request->jalur_id : null;
        $data['prodi_id'] = $request->filled('prodi_id') ? $request->prodi_id : null;
        $data['wajib'] = $request->boolean('wajib');
        $data['is_active'] = $request->boolean('is_active');

        $dokumen->update($data);

        return $this->ajaxSuccess($request, 'Dokumen persyaratan berhasil diperbarui.', 'admin.dokumen.index');
    }

    public function destroy(Request $request, DokumenPersyaratan $dokumen): JsonResponse|RedirectResponse
    {
        $dokumen->delete();

        return $this->ajaxSuccess($request, 'Dokumen persyaratan berhasil dihapus.', 'admin.dokumen.index');
    }
}
