<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtJadwal;
use App\Models\CbtSoal;
use App\Models\Gelombang;
use App\Models\Jalur;
use App\Models\Prodi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CbtJadwalController extends Controller
{
    public function index(Request $request): View
    {
        $jadwal = CbtJadwal::with(['jalur', 'gelombang', 'prodi', 'komposisi'])
            ->withCount('sesi')
            ->when($request->filled('jalur_id'), fn ($q) => $q->where('jalur_id', $request->jalur_id))
            ->orderByDesc('waktu_mulai')
            ->paginate(20)
            ->withQueryString();

        $jalurList = Jalur::orderBy('urutan')->get();

        return view('admin.cbt.jadwal.index', compact('jadwal', 'jalurList'));
    }

    public function create(): View
    {
        return view('admin.cbt.jadwal.form', [
            'jadwal' => new CbtJadwal,
            'jalurList' => Jalur::where('is_active', true)->orderBy('urutan')->get(),
            'gelombangList' => Gelombang::orderByDesc('tanggal_mulai')->get(),
            'prodiList' => Prodi::where('is_active', true)->orderBy('nama')->get(),
            'kategoriList' => $this->kategoriList(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$data, $komposisi] = $this->validated($request);

        DB::transaction(function () use ($data, $komposisi) {
            $jadwal = CbtJadwal::create($data);
            $jadwal->komposisi()->createMany($komposisi);
        });

        return redirect()->route('admin.cbt-jadwal.index')->with('success', 'Jadwal CBT berhasil ditambahkan.');
    }

    public function edit(CbtJadwal $jadwal): View
    {
        $jadwal->load('komposisi');

        return view('admin.cbt.jadwal.form', [
            'jadwal' => $jadwal,
            'jalurList' => Jalur::where('is_active', true)->orderBy('urutan')->get(),
            'gelombangList' => Gelombang::orderByDesc('tanggal_mulai')->get(),
            'prodiList' => Prodi::where('is_active', true)->orderBy('nama')->get(),
            'kategoriList' => $this->kategoriList(),
        ]);
    }

    public function update(Request $request, CbtJadwal $jadwal): RedirectResponse
    {
        [$data, $komposisi] = $this->validated($request);

        DB::transaction(function () use ($jadwal, $data, $komposisi) {
            $jadwal->update($data);
            $jadwal->komposisi()->delete();
            $jadwal->komposisi()->createMany($komposisi);
        });

        return redirect()->route('admin.cbt-jadwal.index')->with('success', 'Jadwal CBT berhasil diperbarui.');
    }

    public function destroy(CbtJadwal $jadwal): RedirectResponse
    {
        $jadwal->delete();

        return redirect()->route('admin.cbt-jadwal.index')->with('success', 'Jadwal CBT berhasil dihapus.');
    }

    /**
     * @return array{0: array, 1: array}
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'jalur_id' => 'required|exists:jalur,id',
            'gelombang_id' => 'nullable|exists:gelombang,id',
            'prodi_id' => 'nullable|exists:prodi,id',
            'nama' => 'required|string|max:150',
            'durasi_menit' => 'required|integer|min:1|max:1440',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'nilai_kelulusan_minimum' => 'nullable|numeric|min:0|max:100',
            'komposisi' => 'required|array|min:1',
            'komposisi.*.kategori' => 'required|string|max:50',
            'komposisi.*.jumlah' => 'required|integer|min:1|max:200',
            'komposisi.*.jumlah_prodi' => 'nullable|integer|min:0|max:200',
        ]);

        $komposisi = array_map(fn ($k) => [
            'kategori' => $k['kategori'],
            'jumlah' => $k['jumlah'],
            'jumlah_prodi' => $k['jumlah_prodi'] ?? 0,
        ], $data['komposisi']);
        unset($data['komposisi']);

        $kategoriUnik = array_unique(array_map(fn ($k) => mb_strtolower(trim($k['kategori'])), $komposisi));
        if (count($kategoriUnik) !== count($komposisi)) {
            throw ValidationException::withMessages([
                'komposisi' => 'Kategori pada komposisi soal tidak boleh duplikat.',
            ]);
        }

        // Kuota khusus prodi hanya masuk akal bila jadwal ini memang menargetkan satu prodi.
        if (blank($data['prodi_id'] ?? null)) {
            $adaKuotaProdi = collect($komposisi)->contains(fn ($k) => $k['jumlah_prodi'] > 0);
            if ($adaKuotaProdi) {
                throw ValidationException::withMessages([
                    'prodi_id' => 'Pilih Program Studi target dulu bila ingin mengisi kuota soal khusus prodi.',
                ]);
            }
        }

        $data['is_active'] = $request->boolean('is_active', true);

        return [$data, $komposisi];
    }

    private function kategoriList()
    {
        return CbtSoal::query()->whereNotNull('kategori')->distinct()->orderBy('kategori')->pluck('kategori');
    }
}
