<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        ]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'digits_between:10,15', 'regex:/^(0|62)/', 'unique:'.User::class.',phone'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('mahasiswa');

        event(new Registered($user));

        Auth::login($user);

        // Kirim email selamat datang ke pendaftar (tidak memblokir alur registrasi).
        app(PendaftaranNotificationService::class)->sendSelamatDatang($user);

        return redirect()->route('mahasiswa.dashboard');
    }
}
