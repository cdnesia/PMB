<?php

namespace App\Http\Controllers;

use App\Models\Referrer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    /**
     * Pencarian kode referral aktif untuk dropdown select2 (dipakai di form registrasi).
     */
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q'));

        if ($term === '') {
            return response()->json(['results' => []]);
        }

        $referrer = Referrer::query()
            ->where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('kode', 'like', "%{$term}%")
                    ->orWhere('nama_instansi', 'like', "%{$term}%");
            })
            ->with('user')
            ->orderBy('kode')
            ->limit(10)
            ->get();

        $results = $referrer->map(fn (Referrer $r) => [
            'id' => $r->kode,
            'text' => $r->kode.' — '.($r->nama_instansi ?? $r->user?->name),
        ]);

        return response()->json(['results' => $results]);
    }
}
