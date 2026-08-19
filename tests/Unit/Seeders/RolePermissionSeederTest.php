<?php

namespace Tests\Unit\Seeders;

use App\Models\Referrer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_karyawan_and_mitra_roles_get_dashboard_referrer_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->assertTrue(Role::findByName('karyawan')->hasPermissionTo('dashboard-referrer'));
        $this->assertTrue(Role::findByName('mitra')->hasPermissionTo('dashboard-referrer'));
    }

    public function test_seeding_creates_demo_karyawan_and_mitra_referrers(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $karyawan = User::where('email', 'karyawan@pmb.test')->firstOrFail();
        $mitra = User::where('email', 'mitra@pmb.test')->firstOrFail();

        $this->assertTrue($karyawan->hasRole('karyawan'));
        $this->assertTrue($mitra->hasRole('mitra'));

        $this->assertDatabaseHas('referrer', [
            'user_id' => $karyawan->id,
            'kode' => 'REF-KARYAWAN',
            'jenis' => 'karyawan',
            'nama_instansi' => null,
        ]);

        $this->assertDatabaseHas('referrer', [
            'user_id' => $mitra->id,
            'kode' => 'REF-MITRA',
            'jenis' => 'mitra',
            'nama_instansi' => 'SMA Negeri 1 Jambi',
        ]);
    }

    public function test_seeding_is_idempotent(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $this->assertSame(1, User::where('email', 'karyawan@pmb.test')->count());
        $this->assertSame(1, User::where('email', 'mitra@pmb.test')->count());
        $this->assertSame(2, Referrer::count());
    }
}
