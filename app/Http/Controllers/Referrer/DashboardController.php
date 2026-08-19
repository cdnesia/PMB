<?php

namespace App\Http\Controllers\Referrer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $referrer = Auth::user()->referrerProfile()->firstOrFail();

        $pendaftar = $referrer->pendaftaran()
            ->with('user')
            ->latest()
            ->get();

        return view('referrer.dashboard', [
            'referrer' => $referrer,
            'pendaftar' => $pendaftar,
        ]);
    }
}
