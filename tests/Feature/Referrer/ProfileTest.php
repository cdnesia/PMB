<?php

namespace Tests\Feature\Referrer;

use App\Models\Referrer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_referrer_can_view_their_profile_and_bank_info(): void
    {
        $user = User::factory()->create();
        $user->assignRole('mitra');
        Referrer::factory()->mitra()->create([
            'user_id' => $user->id,
            'kode' => 'REF-TEST-M',
            'nama_bank' => 'BCA',
            'nomor_rekening' => '1234567890',
            'nama_pemilik_rekening' => 'Budi Santoso',
        ]);

        $response = $this->actingAs($user)->get(route('referrer.profile.edit'));

        $response->assertOk();
        $response->assertSee('REF-TEST-M');
        $response->assertSee('BCA');
        $response->assertSee('1234567890');
        $response->assertSee('Budi Santoso');
    }

    public function test_referrer_can_update_their_profile_and_bank_info(): void
    {
        $user = User::factory()->create(['name' => 'Lama']);
        $user->assignRole('mitra');
        $referrer = Referrer::factory()->mitra()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('referrer.profile.update'), [
            'name' => 'Baru',
            'phone' => '081234567890',
            'nama_instansi' => 'PT Contoh',
            'nama_bank' => 'Mandiri',
            'nomor_rekening' => '9876543210',
            'nama_pemilik_rekening' => 'Baru',
        ]);

        $response->assertRedirect(route('referrer.profile.edit'));

        $user->refresh();
        $referrer->refresh();

        $this->assertSame('Baru', $user->name);
        $this->assertSame('081234567890', $user->phone);
        $this->assertSame('Mandiri', $referrer->nama_bank);
        $this->assertSame('9876543210', $referrer->nomor_rekening);
        $this->assertSame('Baru', $referrer->nama_pemilik_rekening);
    }

    public function test_referrer_can_change_their_password(): void
    {
        $user = User::factory()->create();
        $user->assignRole('karyawan');
        Referrer::factory()->karyawan()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('referrer.profile.update'), [
            'name' => $user->name,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('referrer.profile.edit'));

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_mahasiswa_cannot_access_referrer_profile(): void
    {
        $mahasiswa = User::factory()->create();
        $mahasiswa->assignRole('mahasiswa');

        $response = $this->actingAs($mahasiswa)->get(route('referrer.profile.edit'));

        $response->assertForbidden();
    }
}
