<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WilayahController extends Controller
{
    /**
     * Ambil daftar wilayah berdasarkan parent (untuk cascading dropdown)
     * atau berdasarkan level (mis. level=negara untuk daftar negara).
     * - level=negara → daftar negara.
     * - level=kecamatan + q → pencarian gabungan kecamatan/kota/provinsi (lihat searchKecamatan()).
     * - Tanpa parent_id → provinsi.
     * - parent_id = id provinsi → kota.
     * - parent_id = id kota → kecamatan.
     */
    public function index(Request $request): JsonResponse
    {
        $parentId = $request->filled('parent_id') ? $request->parent_id : null;
        $level = $request->filled('level') ? $request->level : null;

        if ($level === Wilayah::LEVEL_KECAMATAN && $request->filled('q')) {
            return response()->json($this->searchKecamatan($request->input('q')));
        }

        $query = Wilayah::query()
            ->select('id', 'kode', 'nama', 'level');

        if ($level) {
            $query->where('level', $level);
        } elseif ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->where('level', Wilayah::LEVEL_PROVINSI);
        }

        $wilayah = $query->orderBy('kode')->get();

        return response()->json($wilayah);
    }

    /**
     * Cari kecamatan berdasarkan nama kecamatan, kota/kabupaten, ATAU provinsinya —
     * supaya pendaftar bisa langsung ketik "Kuranji" atau "Padang" tanpa harus
     * memilih provinsi & kota satu-satu. Hasilnya sudah membawa kota_id/provinsi_id
     * agar kedua field tersebut bisa diisi otomatis di client.
     */
    private function searchKecamatan(string $q): array
    {
        return DB::table('wilayah as kecamatan')
            ->join('wilayah as kota', 'kota.id', '=', 'kecamatan.parent_id')
            ->join('wilayah as provinsi', 'provinsi.id', '=', 'kota.parent_id')
            ->where('kecamatan.level', Wilayah::LEVEL_KECAMATAN)
            ->where(function ($query) use ($q) {
                $query->where('kecamatan.nama', 'like', "%{$q}%")
                    ->orWhere('kota.nama', 'like', "%{$q}%")
                    ->orWhere('provinsi.nama', 'like', "%{$q}%");
            })
            ->orderBy('provinsi.nama')
            ->orderBy('kota.nama')
            ->orderBy('kecamatan.nama')
            ->limit(30)
            ->get([
                'kecamatan.id',
                'kecamatan.nama',
                'kota.id as kota_id',
                'kota.nama as kota_nama',
                'provinsi.id as provinsi_id',
                'provinsi.nama as provinsi_nama',
            ])
            ->map(fn ($w) => [
                'id' => $w->id,
                'text' => "{$w->nama}, {$w->kota_nama}, {$w->provinsi_nama}",
                'kota_id' => $w->kota_id,
                'kota_nama' => $w->kota_nama,
                'provinsi_id' => $w->provinsi_id,
                'provinsi_nama' => $w->provinsi_nama,
            ])
            ->all();
    }
}
