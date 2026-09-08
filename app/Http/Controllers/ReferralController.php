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
                    ->orWhere('nama_instansi', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$term}%"));
            })
            ->with('user')
            ->orderBy('kode')
            ->limit(10)
            ->get();

        $results = $referrer->map(function (Referrer $r) {
            $label = $r->user?->name;

            if ($r->nama_instansi) {
                $label = $label ? "{$label} · {$r->nama_instansi}" : $r->nama_instansi;
            }

            return [
                'id' => $r->kode,
                'text' => $label ? "{$r->kode} — {$label}" : $r->kode,
            ];
        });

        return response()->json(['results' => $results]);
    }
}
