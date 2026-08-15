<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    /**
     * Ambil daftar wilayah berdasarkan parent (untuk cascading dropdown)
     * atau berdasarkan level (mis. level=negara untuk daftar negara).
     * - level=negara → daftar negara.
     * - Tanpa parent_id → provinsi.
     * - parent_id = id provinsi → kota.
     * - parent_id = id kota → kecamatan.
     */
    public function index(Request $request): JsonResponse
    {
        $parentId = $request->filled('parent_id') ? $request->parent_id : null;
        $level = $request->filled('level') ? $request->level : null;

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
}
