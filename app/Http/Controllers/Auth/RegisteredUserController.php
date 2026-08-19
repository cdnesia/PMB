<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Referrer;
use App\Models\User;
use App\Services\PendaftaranNotificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Normalisasi nomor WA/telepon: buang spasi, strip, dan tanda hubung.
        $request->merge([
            'phone' => preg_replace('/[^0-9]/', '', $request->input('phone', '')),
            'kode_referral' => $request->filled('kode_referral') ? strtoupper(trim($request->input('kode_referral'))) : null,
        ]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'digits_between:10,15', 'regex:/^(0|62)/', 'unique:'.User::class.',phone'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'kode_referral' => ['nullable', 'string', 'max:30'],
        ]);

        $referrer = null;
        if ($request->filled('kode_referral')) {
            $referrer = Referrer::where('kode', $request->kode_referral)->where('is_active', true)->first();

            if (! $referrer) {
                throw ValidationException::withMessages([
                    'kode_referral' => 'Kode referral tidak ditemukan atau sudah tidak aktif.',
                ]);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'referrer_id' => $referrer?->id,
        ]);

        $user->assignRole('mahasiswa');

        event(new Registered($user));

        Auth::login($user);

        // Kirim email selamat datang ke pendaftar (tidak memblokir alur registrasi).
        app(PendaftaranNotificationService::class)->sendSelamatDatang($user);

        return redirect()->route('mahasiswa.dashboard');
    }
}
