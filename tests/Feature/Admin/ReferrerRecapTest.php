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

class ReferrerRecapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_view_referrer_recap(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('admin.referrer.index'));

        $response->assertOk();
        $response->assertSee('REF-MITRA');
        $response->assertSee('REF-KARYAWAN');
    }

    public function test_admin_pmb_can_view_referrer_recap(): void
    {
        $adminPmb = User::where('email', 'adminpmb@pmb.test')->firstOrFail();

        $response = $this->actingAs($adminPmb)->get(route('admin.referrer.index'));

        $response->assertOk();
    }

    public function test_recap_shows_aggregated_counts_per_referrer(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();
        $referrer = Referrer::where('kode', 'REF-MITRA')->firstOrFail();

        $tahun = TahunPenerimaan::create(['kode' => '2026/2027', 'nama' => 'Tahun 2026/2027']);
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler']);

        Pendaftaran::forceCreate([
            'no_urut' => 1,
            'user_id' => User::factory()->create()->id,
            'tahun_id' => $tahun->id,
            'jalur_id' => $jalur->id,
            'referrer_id' => $referrer->id,
            'status' => 'lolos',
            'status_pembayaran' => 'lunas',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.referrer.index'));

        $response->assertOk();
        $response->assertViewHas('referrer', function ($paginated) use ($referrer) {
            $row = $paginated->firstWhere('id', $referrer->id);

            return $row->pendaftaran_count === 1 && $row->lunas_count === 1 && $row->lolos_count === 1;
        });
    }

    public function test_referrer_role_cannot_access_admin_recap(): void
    {
        $mitra = User::where('email', 'mitra@pmb.test')->firstOrFail();

        $response = $this->actingAs($mitra)->get(route('admin.referrer.index'));

        $response->assertForbidden();
    }

    public function test_show_lists_students_referred_by_a_specific_referrer(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();
        $referrer = Referrer::where('kode', 'REF-MITRA')->firstOrFail();

        $tahun = TahunPenerimaan::create(['kode' => '2026/2027', 'nama' => 'Tahun 2026/2027']);
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler']);
        $mahasiswa = User::factory()->create(['name' => 'Budi Santoso']);

        Pendaftaran::forceCreate([
            'no_urut' => 1,
            'user_id' => $mahasiswa->id,
            'tahun_id' => $tahun->id,
            'jalur_id' => $jalur->id,
            'referrer_id' => $referrer->id,
            'status' => 'lolos',
            'status_pembayaran' => 'lunas',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.referrer.show', $referrer));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
    }
}
