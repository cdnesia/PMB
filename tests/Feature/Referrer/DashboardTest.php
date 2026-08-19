<?php

namespace Tests\Feature\Referrer;

use App\Models\Jalur;
use App\Models\Pendaftaran;
use App\Models\Referrer;
use App\Models\TahunPenerimaan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_karyawan_can_view_their_referrer_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('karyawan');
        $referrer = Referrer::factory()->karyawan()->create(['user_id' => $user->id, 'kode' => 'REF-TEST-K']);

        $response = $this->actingAs($user)->get(route('referrer.dashboard'));

        $response->assertOk();
        $response->assertSee('REF-TEST-K');
    }

    public function test_mitra_can_view_their_referrer_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('mitra');
        Referrer::factory()->mitra()->create(['user_id' => $user->id, 'kode' => 'REF-TEST-M']);

        $response = $this->actingAs($user)->get(route('referrer.dashboard'));

        $response->assertOk();
        $response->assertSee('REF-TEST-M');
    }

    public function test_dashboard_lists_students_who_used_the_referral_code_with_payment_status(): void
    {
        $referrerUser = User::factory()->create();
        $referrerUser->assignRole('mitra');
        $referrer = Referrer::factory()->mitra()->create(['user_id' => $referrerUser->id]);

        $tahun = TahunPenerimaan::create(['kode' => '2026/2027', 'nama' => 'Tahun 2026/2027']);
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler']);

        $mahasiswa = User::factory()->create(['name' => 'Rina Amelia']);
        Pendaftaran::forceCreate([
            'no_urut' => 1,
            'user_id' => $mahasiswa->id,
            'tahun_id' => $tahun->id,
            'jalur_id' => $jalur->id,
            'referrer_id' => $referrer->id,
            'nomor_pendaftaran' => 'PMB-TEST-00001',
            'status' => 'lolos',
            'status_pembayaran' => 'lunas',
        ]);

        $response = $this->actingAs($referrerUser)->get(route('referrer.dashboard'));

        $response->assertOk();
        $response->assertSee('Rina Amelia');
        $response->assertSeeText('Lunas');
    }

    public function test_mahasiswa_cannot_access_referrer_dashboard(): void
    {
        $mahasiswa = User::factory()->create();
        $mahasiswa->assignRole('mahasiswa');

        $response = $this->actingAs($mahasiswa)->get(route('referrer.dashboard'));

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('referrer.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_generic_dashboard_route_redirects_karyawan_to_referrer_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('karyawan');
        Referrer::factory()->karyawan()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('referrer.dashboard'));
    }
}
