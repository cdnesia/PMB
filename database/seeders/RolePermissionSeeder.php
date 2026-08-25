<?php

namespace Database\Seeders;

use App\Models\Referrer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

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
            'kelola-cbt',
            'kelola-user',
            'kelola-referrer',
            'dashboard-mahasiswa',
            'pendaftaran-mahasiswa',
            'dashboard-referrer',
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
            'kelola-cbt',
            'kelola-referrer',
        ]);

        $karyawan = Role::findOrCreate('karyawan');
        $karyawan->syncPermissions(['dashboard-referrer']);

        $mitra = Role::findOrCreate('mitra');
        $mitra->syncPermissions(['dashboard-referrer']);

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

        // Referrer demo (karyawan & mitra) untuk memudahkan pengujian alur referral.
        $this->seedReferrer('Karyawan Referral', 'karyawan@pmb.test', 'password', 'karyawan', 'REF-KARYAWAN', null);
        $this->seedReferrer('Mitra Sekolah', 'mitra@pmb.test', 'password', 'mitra', 'REF-MITRA', 'SMA Negeri 1 Jambi');
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

    private function seedReferrer(string $name, string $email, string $password, string $jenis, string $kode, ?string $instansi): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole($jenis);

        Referrer::firstOrCreate(
            ['user_id' => $user->id],
            [
                'kode' => $kode,
                'jenis' => $jenis,
                'nama_instansi' => $instansi,
                'is_active' => true,
            ]
        );
    }
}
