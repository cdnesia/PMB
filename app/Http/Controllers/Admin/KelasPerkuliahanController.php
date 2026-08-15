<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelasPerkuliahan;
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

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        KelasPerkuliahan::create($data);

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas perkuliahan berhasil ditambahkan.');
    }

    public function edit(KelasPerkuliahan $kelas): View
    {
        return view('admin.kelas.form', compact('kelas'));
    }

    public function update(Request $request, KelasPerkuliahan $kelas): RedirectResponse
    {
        $data = $this->validated($request, $kelas->id);

        $kelas->update($data);

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas perkuliahan berhasil diperbarui.');
    }

    public function destroy(KelasPerkuliahan $kelas): RedirectResponse
    {
        $kelas->delete();

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas perkuliahan berhasil dihapus.');
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
