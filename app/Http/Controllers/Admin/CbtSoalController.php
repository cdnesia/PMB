<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtSoal;
use App\Models\Jalur;
use App\Models\Prodi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CbtSoalController extends Controller
{
    public function index(Request $request): View
    {
        $soal = CbtSoal::with(['jalur', 'prodi'])
            ->when($request->filled('jalur_id'), fn ($q) => $q->where('jalur_id', $request->jalur_id))
            ->when($request->filled('prodi_id'), fn ($q) => $q->where('prodi_id', $request->prodi_id))
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori', $request->kategori))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $jalurList = Jalur::orderBy('urutan')->get();
        $prodiList = Prodi::orderBy('nama')->get();
        $kategoriList = $this->kategoriList();

        return view('admin.cbt.soal.index', compact('soal', 'jalurList', 'prodiList', 'kategoriList'));
    }

    public function create(): View
    {
        return view('admin.cbt.soal.form', [
            'soal' => new CbtSoal,
            'jalurList' => Jalur::where('is_active', true)->orderBy('urutan')->get(),
            'prodiList' => Prodi::where('is_active', true)->orderBy('nama')->get(),
            'kategoriList' => $this->kategoriList(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        CbtSoal::create($this->validated($request));

        return redirect()->route('admin.cbt-soal.index')->with('success', 'Soal CBT berhasil ditambahkan.');
    }

    public function edit(CbtSoal $soal): View
    {
        return view('admin.cbt.soal.form', [
            'soal' => $soal,
            'jalurList' => Jalur::where('is_active', true)->orderBy('urutan')->get(),
            'prodiList' => Prodi::where('is_active', true)->orderBy('nama')->get(),
            'kategoriList' => $this->kategoriList(),
        ]);
    }

    public function update(Request $request, CbtSoal $soal): RedirectResponse
    {
        $soal->update($this->validated($request));

        return redirect()->route('admin.cbt-soal.index')->with('success', 'Soal CBT berhasil diperbarui.');
    }

    public function destroy(CbtSoal $soal): RedirectResponse
    {
        $soal->delete();

        return redirect()->route('admin.cbt-soal.index')->with('success', 'Soal CBT berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'jalur_id' => 'nullable|exists:jalur,id',
            'prodi_id' => 'nullable|exists:prodi,id',
            'kategori' => 'required|string|max:50',
            'pertanyaan' => 'required|string',
            'pilihan_a' => 'required|string',
            'pilihan_b' => 'required|string',
            'pilihan_c' => 'required|string',
            'pilihan_d' => 'required|string',
            'pilihan_e' => 'nullable|string',
            'kunci_jawaban' => 'required|in:a,b,c,d,e',
            'bobot' => 'required|numeric|min:0.01|max:999.99',
        ]);

        // Kunci jawaban harus mengacu ke pilihan yang benar-benar terisi.
        if ($data['kunci_jawaban'] === 'e' && blank($data['pilihan_e'] ?? null)) {
            throw ValidationException::withMessages([
                'kunci_jawaban' => 'Pilihan E belum diisi, tidak bisa dijadikan kunci jawaban.',
            ]);
        }

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    /**
     * Daftar kategori yang sudah pernah dipakai, untuk saran (datalist) di form soal & komposisi jadwal.
     */
    private function kategoriList()
    {
        return CbtSoal::query()->whereNotNull('kategori')->distinct()->orderBy('kategori')->pluck('kategori');
    }
}
