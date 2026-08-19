<?php

namespace Tests\Feature\Admin;

use App\Models\Jalur;
use App\Models\Pendaftaran;
use App\Models\Referrer;
use App\Models\TahunPenerimaan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_view_user_list(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('admin.user.index'));

        $response->assertOk();
        $response->assertSee('Super Admin');
    }

    public function test_admin_pmb_cannot_access_user_management(): void
    {
        $adminPmb = User::where('email', 'adminpmb@pmb.test')->firstOrFail();

        $response = $this->actingAs($adminPmb)->get(route('admin.user.index'));

        $response->assertForbidden();
    }

    public function test_super_admin_can_create_a_plain_user(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.user.store'), [
            'name' => 'Verifikator Baru',
            'email' => 'verifikator.baru@pmb.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'verifikator',
        ]);

        $response->assertRedirect(route('admin.user.index'));

        $user = User::where('email', 'verifikator.baru@pmb.test')->firstOrFail();
        $this->assertTrue($user->hasRole('verifikator'));
        $this->assertNull($user->referrerProfile);
    }

    public function test_creating_a_karyawan_user_also_creates_a_referrer_profile(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.user.store'), [
            'name' => 'Karyawan Baru',
            'email' => 'karyawan.baru@pmb.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'karyawan',
            'kode' => 'REF-BARU',
        ]);

        $response->assertRedirect(route('admin.user.index'));

        $user = User::where('email', 'karyawan.baru@pmb.test')->firstOrFail();
        $this->assertTrue($user->hasRole('karyawan'));
        $this->assertNotNull($user->referrerProfile);
        $this->assertSame('REF-BARU', $user->referrerProfile->kode);
        $this->assertTrue($user->referrerProfile->is_active);
    }

    public function test_creating_a_karyawan_user_without_kode_fails_validation(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.user.store'), [
            'name' => 'Karyawan Tanpa Kode',
            'email' => 'karyawan.nokode@pmb.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'karyawan',
        ]);

        $response->assertSessionHasErrors('kode');
        $this->assertDatabaseMissing('users', ['email' => 'karyawan.nokode@pmb.test']);
    }

    public function test_super_admin_can_update_a_users_role_and_referrer_kode(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();
        $mitraUser = User::where('email', 'mitra@pmb.test')->firstOrFail();

        $response = $this->actingAs($admin)->put(route('admin.user.update', $mitraUser), [
            'name' => $mitraUser->name,
            'email' => $mitraUser->email,
            'role' => 'mitra',
            'kode' => 'REF-MITRA-UPDATED',
            'nama_instansi' => 'SMA Negeri 2 Jambi',
        ]);

        $response->assertRedirect(route('admin.user.index'));

        $mitraUser->refresh();
        $this->assertSame('REF-MITRA-UPDATED', $mitraUser->referrerProfile->kode);
        $this->assertSame('SMA Negeri 2 Jambi', $mitraUser->referrerProfile->nama_instansi);
    }

    public function test_password_is_unchanged_when_left_blank_on_update(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();
        $user = User::factory()->create(['password' => bcrypt('original-password')]);
        $user->assignRole('verifikator');
        $originalHash = $user->password;

        $response = $this->actingAs($admin)->put(route('admin.user.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'verifikator',
        ]);

        $response->assertRedirect(route('admin.user.index'));
        $this->assertSame($originalHash, $user->refresh()->password);
    }

    public function test_super_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();

        $response = $this->actingAs($admin)->delete(route('admin.user.destroy', $admin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertNotNull($admin->fresh());
    }

    public function test_cannot_delete_a_user_who_has_pendaftaran(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();
        $mahasiswa = User::factory()->create();
        $mahasiswa->assignRole('mahasiswa');

        $tahun = TahunPenerimaan::create(['kode' => '2026/2027', 'nama' => 'Tahun 2026/2027']);
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler']);
        Pendaftaran::forceCreate([
            'no_urut' => 1,
            'user_id' => $mahasiswa->id,
            'tahun_id' => $tahun->id,
            'jalur_id' => $jalur->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.user.destroy', $mahasiswa));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertNotNull($mahasiswa->fresh());
    }

    public function test_super_admin_can_delete_a_user_without_pendaftaran(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();
        $verifikator = User::factory()->create();
        $verifikator->assignRole('verifikator');

        $response = $this->actingAs($admin)->delete(route('admin.user.destroy', $verifikator));

        $response->assertRedirect(route('admin.user.index'));
        $this->assertNull($verifikator->fresh());
    }
}
