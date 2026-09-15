<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Role bawaan yang dipakai langsung di kode (middleware route, pengecekan
     * hasRole di controller) sehingga tidak boleh dihapus atau diganti nama.
     * Hak aksesnya tetap bisa disesuaikan, kecuali super-admin yang selalu
     * memegang seluruh permission agar panitia tidak terkunci dari sistem.
     */
    private const PROTECTED_ROLES = ['super-admin', 'admin-pmb', 'mahasiswa', 'karyawan', 'mitra'];

    public function index(): View
    {
        $roles = Role::withCount('users')->with('permissions')->orderBy('name')->get();

        return view('admin.role.index', [
            'roles' => $roles,
            'permissionGroups' => $this->permissionGroups(),
            'protectedRoles' => self::PROTECTED_ROLES,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:60|alpha_dash|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return $this->ajaxSuccess($request, 'Role berhasil ditambahkan.', 'admin.role.index', status: 201);
    }

    public function update(Request $request, Role $role): JsonResponse|RedirectResponse
    {
        if ($role->name === 'super-admin') {
            return $this->forbidden($request, 'Role Super Admin selalu memegang seluruh hak akses dan tidak dapat diubah.');
        }

        $isProtected = in_array($role->name, self::PROTECTED_ROLES, true);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:roles,name,'.$role->id],
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if (! $isProtected) {
            $role->name = $data['name'];
            $role->save();
        }

        $role->syncPermissions($data['permissions'] ?? []);

        return $this->ajaxSuccess($request, 'Role berhasil diperbarui.', 'admin.role.index');
    }

    public function destroy(Request $request, Role $role): JsonResponse|RedirectResponse
    {
        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return $this->forbidden($request, 'Role "'.$role->name.'" adalah role bawaan sistem dan tidak dapat dihapus.');
        }

        $jumlahUser = $role->users()->count();

        if ($jumlahUser > 0) {
            return $this->forbidden($request, "Role ini masih dipakai oleh {$jumlahUser} user. Pindahkan role user tersebut terlebih dahulu.");
        }

        $role->delete();

        return $this->ajaxSuccess($request, 'Role berhasil dihapus.', 'admin.role.index');
    }

    private function forbidden(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return back()->with('error', $message);
    }

    /**
     * Kelompokkan permission untuk tampilan checklist di form role supaya
     * lebih mudah dibaca dibanding daftar datar. Permission baru yang belum
     * dipetakan otomatis masuk ke grup "Lainnya" agar tidak pernah hilang
     * dari UI meski map di bawah belum diperbarui.
     */
    private function permissionGroups(): array
    {
        $map = [
            'Dashboard' => ['dashboard-admin', 'dashboard-referrer', 'dashboard-mahasiswa'],
            'Akademik & Pendaftaran' => [
                'kelola-tahun', 'kelola-jalur', 'kelola-prodi', 'kelola-kelas',
                'kelola-gelombang', 'kelola-kuota', 'kelola-setting-prodi',
                'kelola-pendaftaran', 'kelola-pengumuman',
            ],
            'Tes CBT' => ['kelola-cbt'],
            'Sistem & Pengguna' => ['kelola-user', 'kelola-role', 'kelola-referrer'],
            'Mahasiswa' => ['pendaftaran-mahasiswa'],
        ];

        $allPermissions = Permission::orderBy('name')->pluck('name')->all();
        $grouped = [];

        foreach ($map as $label => $names) {
            $available = array_values(array_intersect($names, $allPermissions));

            if ($available) {
                $grouped[$label] = $available;
            }
        }

        $mapped = collect($map)->flatten()->all();
        $sisa = array_values(array_diff($allPermissions, $mapped));

        if ($sisa) {
            $grouped['Lainnya'] = $sisa;
        }

        return $grouped;
    }
}
