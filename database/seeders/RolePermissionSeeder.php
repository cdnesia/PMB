<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard-admin',
            'kelola-tahun',
            'kelola-jalur',
            'kelola-prodi',
            'kelola-kelas',
            'kelola-kuota',
            'kelola-setting-prodi',
            'kelola-gelombang',
            'kelola-pendaftaran',
            'kelola-pengumuman',
            'kelola-user',
            'dashboard-mahasiswa',
            'pendaftaran-mahasiswa',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $superAdmin = Role::findOrCreate('super-admin');
        $superAdmin->syncPermissions(Permission::all());

        $adminPmb = Role::findOrCreate('admin-pmb');
        $adminPmb->syncPermissions([
            'dashboard-admin',
            'kelola-tahun',
            'kelola-jalur',
            'kelola-prodi',
            'kelola-kelas',
            'kelola-kuota',
            'kelola-setting-prodi',
            'kelola-gelombang',
            'kelola-pendaftaran',
            'kelola-pengumuman',
        ]);

        Role::findOrCreate('operator-prodi');
        Role::findOrCreate('verifikator');
        Role::findOrCreate('bendahara');
        Role::findOrCreate('pimpinan');

        $mahasiswa = Role::findOrCreate('mahasiswa');
        $mahasiswa->syncPermissions([
            'dashboard-mahasiswa',
            'pendaftaran-mahasiswa',
        ]);

        // Pengguna bawaan untuk pengembangan
        $this->seedUser('Super Admin', 'admin@pmb.test', 'password', 'super-admin');
        $this->seedUser('Admin PMB', 'adminpmb@pmb.test', 'password', 'admin-pmb');
        $this->seedUser('Mahasiswa', 'mahasiswa@pmb.test', 'password', 'mahasiswa');
    }

    private function seedUser(string $name, string $email, string $password, string $role): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole($role);
    }
}
