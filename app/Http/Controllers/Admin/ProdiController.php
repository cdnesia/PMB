<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProdiController extends Controller
{
    public function index(): View
    {
        $prodi = Prodi::orderBy('jenjang')->orderBy('nama')->paginate(20);

        return view('admin.prodi.index', compact('prodi'));
    }

    public function create(): View
    {
        return view('admin.prodi.form', ['prodi' => new Prodi()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Prodi::create($data);

        return redirect()->route('admin.prodi.index')->with('success', 'Prodi berhasil ditambahkan.');
    }

    public function edit(Prodi $prodi): View
    {
        return view('admin.prodi.form', compact('prodi'));
    }

    public function update(Request $request, Prodi $prodi): RedirectResponse
    {
        $data = $this->validated($request, $prodi->id);

        $prodi->update($data);

        return redirect()->route('admin.prodi.index')->with('success', 'Prodi berhasil diperbarui.');
    }

    public function destroy(Prodi $prodi): RedirectResponse
    {
        $prodi->delete();

        return redirect()->route('admin.prodi.index')->with('success', 'Prodi berhasil dihapus.');
    }

    private function validated(Request $request, ?string $id = null): array
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20|unique:prodi,kode'.($id ? ','.$id : ''),
            'nama' => 'required|string|max:100',
            'jenjang' => 'required|in:D3,D4,S1,S2',
            'fakultas' => 'nullable|string|max:100',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
