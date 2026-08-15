<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DokumenPersyaratan;
use App\Models\Jalur;
use App\Models\Prodi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'jalur_id' => 'nullable|exists:jalur,id',
            'prodi_id' => 'nullable|exists:prodi,id',
            'dokumen' => 'required|array|min:1',
            'dokumen.*.nama' => 'required|string|max:200',
        ]);

        if (! $request->filled('jalur_id') && ! $request->filled('prodi_id')) {
            return back()->withErrors(['jalur_id' => 'Pilih minimal satu: jalur atau prodi.'])->withInput();
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

        return redirect()->route('admin.dokumen.index')->with('success', 'Dokumen persyaratan berhasil ditambahkan.');
    }

    public function edit(DokumenPersyaratan $dokumen): View
    {
        $jalurList = Jalur::orderBy('urutan')->get();
        $prodiList = Prodi::orderBy('jenjang')->orderBy('nama')->get();

        return view('admin.dokumen.edit', compact('dokumen', 'jalurList', 'prodiList'));
    }

    public function update(Request $request, DokumenPersyaratan $dokumen): RedirectResponse
    {
        $data = $request->validate([
            'jalur_id' => 'nullable|exists:jalur,id',
            'prodi_id' => 'nullable|exists:prodi,id',
            'nama' => 'required|string|max:200',
        ]);

        if (! $request->filled('jalur_id') && ! $request->filled('prodi_id')) {
            return back()->withErrors(['jalur_id' => 'Pilih minimal satu: jalur atau prodi.'])->withInput();
        }

        $data['jalur_id'] = $request->filled('jalur_id') ? $request->jalur_id : null;
        $data['prodi_id'] = $request->filled('prodi_id') ? $request->prodi_id : null;
        $data['wajib'] = $request->boolean('wajib');
        $data['is_active'] = $request->boolean('is_active');

        $dokumen->update($data);

        return redirect()->route('admin.dokumen.index')->with('success', 'Dokumen persyaratan berhasil diperbarui.');
    }

    public function destroy(DokumenPersyaratan $dokumen): RedirectResponse
    {
        $dokumen->delete();

        return redirect()->route('admin.dokumen.index')->with('success', 'Dokumen persyaratan berhasil dihapus.');
    }
}
