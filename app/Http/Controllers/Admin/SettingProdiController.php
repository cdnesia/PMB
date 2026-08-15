<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jalur;
use App\Models\KelasPerkuliahan;
use App\Models\Prodi;
use App\Models\ProdiKelasJalur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingProdiController extends Controller
{
    public function index(): View
    {
        $prodi = Prodi::withCount([
            'kelasPerkuliahan as jumlah_kelas' => fn ($q) => $q->select(DB::raw('COUNT(DISTINCT kelas_perkuliahan.id)')),
            'jalur as jumlah_jalur' => fn ($q) => $q->select(DB::raw('COUNT(DISTINCT jalur.id)')),
        ])->orderBy('jenjang')->orderBy('nama')->get();

        return view('admin.setting-prodi.index', compact('prodi'));
    }

    public function edit(Prodi $prodi): View
    {
        $kelasList = KelasPerkuliahan::orderBy('nama')->get();
        $jalurList = Jalur::orderBy('urutan')->get();

        $selected = ProdiKelasJalur::where('prodi_id', $prodi->id)
            ->get()
            ->mapWithKeys(fn ($pkj) => [$pkj->kelas_id.'-'.$pkj->jalur_id => true]);

        return view('admin.setting-prodi.form', compact('prodi', 'kelasList', 'jalurList', 'selected'));
    }

    public function update(Request $request, Prodi $prodi): RedirectResponse
    {
        $request->validate([
            'combos' => 'nullable|array',
            'combos.*' => 'required|in:1',
        ]);

        $rows = [];
        foreach (array_keys($request->input('combos', [])) as $key) {
            [$kelasId, $jalurId] = explode('-', $key);
            $rows[] = [
                'prodi_id' => $prodi->id,
                'kelas_id' => $kelasId,
                'jalur_id' => $jalurId,
            ];
        }

        DB::transaction(function () use ($prodi, $rows) {
            ProdiKelasJalur::where('prodi_id', $prodi->id)->delete();

            // Gunakan create() per baris agar UUID id tergenerate otomatis (HasUuids).
            foreach ($rows as $row) {
                ProdiKelasJalur::create($row);
            }
        });

        return redirect()->route('admin.setting-prodi.index')->with('success', 'Setting prodi "'.$prodi->nama.'" berhasil diperbarui.');
    }
}
