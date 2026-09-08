<?php

namespace Tests\Feature\Admin;

use App\Models\Referrer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAjaxCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@pmb.test')->firstOrFail();
    }

    public function test_store_karyawan_via_ajax_creates_referrer_profile(): void
    {
        $response = $this->actingAs($this->admin())->postJson(route('admin.user.store'), [
            'name' => 'Karyawan Baru',
            'email' => 'karyawan.baru@pmb.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'karyawan',
            'kode' => 'ref-baru',
        ]);

        $response->assertCreated();
        $user = User::where('email', 'karyawan.baru@pmb.test')->firstOrFail();
        $this->assertTrue($user->hasRole('karyawan'));
        $this->assertDatabaseHas('referrer', ['user_id' => $user->id, 'kode' => 'REF-BARU']);
    }

    public function test_store_with_mismatched_password_returns_422(): void
    {
        $response = $this->actingAs($this->admin())->postJson(route('admin.user.store'), [
            'name' => 'User Gagal',
            'email' => 'gagal@pmb.test',
            'password' => 'password123',
            'password_confirmation' => 'lainlain',
            'role' => 'admin-pmb',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_destroy_self_via_ajax_returns_422(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->deleteJson(route('admin.user.destroy', $admin));

        $response->assertStatus(422);
        $this->assertNotNull($admin->fresh());
    }
}
