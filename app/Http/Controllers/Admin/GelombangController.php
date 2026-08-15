<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gelombang;
use App\Models\Jalur;
use App\Models\TahunPenerimaan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GelombangController extends Controller
{
    public function index(Request $request): View
    {
        $gelombang = Gelombang::with(['tahun', 'jalur'])
            ->when($request->filled('tahun_id'), fn ($q) => $q->where('tahun_id', $request->tahun_id))
            ->orderBy('tanggal_mulai')
            ->paginate(20)
            ->withQueryString();

        $tahunList = TahunPenerimaan::orderBy('kode')->get();

        return view('admin.gelombang.index', compact('gelombang', 'tahunList'));
    }

    public function create(): View
    {
        return view('admin.gelombang.form', [
            'gelombang' => new Gelombang(),
            'tahunList' => TahunPenerimaan::orderBy('kode')->get(),
            'jalurList' => Jalur::where('is_active', true)->orderBy('urutan')->get(),
            'selectedJalur' => collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $gelombang = Gelombang::create($data);
        $gelombang->jalur()->sync($request->input('jalur', []));

        return redirect()->route('admin.gelombang.index')->with('success', 'Gelombang berhasil ditambahkan.');
    }

    public function edit(Gelombang $gelombang): View
    {
        return view('admin.gelombang.form', [
            'gelombang' => $gelombang,
            'tahunList' => TahunPenerimaan::orderBy('kode')->get(),
            'jalurList' => Jalur::where('is_active', true)->orderBy('urutan')->get(),
            'selectedJalur' => $gelombang->jalur->pluck('id'),
        ]);
    }

    public function update(Request $request, Gelombang $gelombang): RedirectResponse
    {
        $data = $this->validated($request, $gelombang->id);

        $gelombang->update($data);
        $gelombang->jalur()->sync($request->input('jalur', []));

        return redirect()->route('admin.gelombang.index')->with('success', 'Gelombang berhasil diperbarui.');
    }

    public function destroy(Gelombang $gelombang): RedirectResponse
    {
        $gelombang->delete();

        return redirect()->route('admin.gelombang.index')->with('success', 'Gelombang berhasil dihapus.');
    }

    /**
     * Validasi data gelombang, termasuk penolakan jika tanggal bentrok.
     */
    private function validated(Request $request, ?string $id = null): array
    {
        $data = $request->validate([
            'tahun_id' => 'required|exists:tahun_penerimaan,id',
            'nama' => 'required|string|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'tanggal_pengumuman' => 'nullable|date|after_or_equal:tanggal_selesai',
            'jalur' => 'required|array|min:1',
            'jalur.*' => 'exists:jalur,id',
        ]);

        $mulai = $data['tanggal_mulai'];
        $selesai = $data['tanggal_selesai'];

        // Cek bentrok tanggal dengan gelombang lain di tahun yang sama.
        $konflik = Gelombang::where('tahun_id', $data['tahun_id'])
            ->when($id, fn ($q) => $q->where('id', '!=', $id))
            ->where(function ($q) use ($mulai, $selesai) {
                // Overlap: A.start <= B.end AND B.start <= A.end
                $q->whereBetween('tanggal_mulai', [$mulai, $selesai])
                    ->orWhereBetween('tanggal_selesai', [$mulai, $selesai])
                    ->orWhere(function ($qq) use ($mulai, $selesai) {
                        $qq->where('tanggal_mulai', '<=', $mulai)
                            ->where('tanggal_selesai', '>=', $selesai);
                    });
            })
            ->first();

        if ($konflik) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tanggal_mulai' => 'Rentang tanggal bentrok dengan gelombang "'.$konflik->nama.'" ('.$konflik->tanggal_mulai->format('d/m/Y').' — '.$konflik->tanggal_selesai->format('d/m/Y').').',
            ]);
        }

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
