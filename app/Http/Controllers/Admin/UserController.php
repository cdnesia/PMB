<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referrer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const ROLE_REFERRER = ['karyawan', 'mitra'];

    public function index(Request $request): View
    {
        $users = User::with(['roles', 'referrerProfile'])
            ->when($request->filled('role'), fn ($q) => $q->whereHas('roles', fn ($q) => $q->where('name', $request->input('role'))))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $roles = Role::orderBy('name')->pluck('name');

        return view('admin.user.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        return view('admin.user.form', [
            'user' => new User(),
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($data['role']);
        $this->syncReferrerProfile($user, $data);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $user->load(['roles', 'referrerProfile']);

        return view('admin.user.form', [
            'user' => $user,
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;

        if (filled($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $user->syncRoles([$data['role']]);
        $this->syncReferrerProfile($user, $data);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->hapusBersih();

        return redirect()->route('admin.user.index')->with('success', 'User beserta seluruh data pendaftaran, riwayat, dan file terkait berhasil dihapus.');
    }

    private function syncReferrerProfile(User $user, array $data): void
    {
        if (! in_array($data['role'], self::ROLE_REFERRER, true)) {
            return;
        }

        Referrer::updateOrCreate(
            ['user_id' => $user->id],
            [
                'kode' => $data['kode'],
                'jenis' => $data['role'],
                'nama_instansi' => $data['nama_instansi'] ?? null,
                'is_active' => $data['referrer_is_active'] ?? true,
            ]
        );
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $referrerId = $user?->referrerProfile?->id;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email'.($user ? ','.$user->id : ''),
            'phone' => 'nullable|string|max:20',
            'password' => ($user ? 'nullable' : 'required').'|string|min:8|confirmed',
            'role' => ['required', 'string', 'exists:roles,name'],
            'kode' => 'required_if:role,karyawan,mitra|nullable|string|max:30|unique:referrer,kode'.($referrerId ? ','.$referrerId : ''),
            'nama_instansi' => 'nullable|string|max:255',
        ]);

        $data['referrer_is_active'] = $request->boolean('referrer_is_active', true);

        return $data;
    }
}
