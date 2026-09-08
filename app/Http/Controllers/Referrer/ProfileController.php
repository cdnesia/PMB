<?php

namespace App\Http\Controllers\Referrer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();
        $referrer = $user->referrerProfile()->firstOrFail();

        return view('referrer.profile', [
            'user' => $user,
            'referrer' => $referrer,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $referrer = $user->referrerProfile()->firstOrFail();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'nama_instansi' => 'nullable|string|max:255',
            'nama_bank' => 'nullable|string|max:255',
            'nomor_rekening' => 'nullable|string|max:50',
            'nama_pemilik_rekening' => 'nullable|string|max:255',
        ]);

        $user->name = $data['name'];
        $user->phone = $data['phone'] ?? null;

        if (filled($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $referrer->update([
            'nama_instansi' => $data['nama_instansi'] ?? null,
            'nama_bank' => $data['nama_bank'] ?? null,
            'nomor_rekening' => $data['nomor_rekening'] ?? null,
            'nama_pemilik_rekening' => $data['nama_pemilik_rekening'] ?? null,
        ]);

        return redirect()->route('referrer.profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}
