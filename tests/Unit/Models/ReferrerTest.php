<?php

namespace Tests\Unit\Models;

use App\Models\Jalur;
use App\Models\Pendaftaran;
use App\Models\Referrer;
use App\Models\TahunPenerimaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferrerTest extends TestCase
{
    use RefreshDatabase;

    public function test_referrer_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $referrer = Referrer::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($referrer->user->is($user));
    }

    public function test_user_can_be_referred_by_a_referrer(): void
    {
        $referrer = Referrer::factory()->create();
        $referredUser = User::factory()->create(['referrer_id' => $referrer->id]);

        $this->assertTrue($referredUser->referrer->is($referrer));
    }

    public function test_pendaftaran_belongs_to_a_referrer(): void
    {
        $referrer = Referrer::factory()->mitra()->create();

        $tahun = TahunPenerimaan::create(['kode' => '2026/2027', 'nama' => 'Tahun 2026/2027']);
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler']);

        $pendaftaran = Pendaftaran::forceCreate([
            'no_urut' => 1,
            'user_id' => User::factory()->create()->id,
            'tahun_id' => $tahun->id,
            'jalur_id' => $jalur->id,
            'referrer_id' => $referrer->id,
        ]);

        $this->assertTrue($pendaftaran->referrer->is($referrer));
        $this->assertTrue($referrer->pendaftaran->contains($pendaftaran));
    }

    public function test_is_mitra_reflects_jenis(): void
    {
        $karyawan = Referrer::factory()->karyawan()->make();
        $mitra = Referrer::factory()->mitra()->make();

        $this->assertFalse($karyawan->isMitra());
        $this->assertTrue($mitra->isMitra());
    }

    public function test_is_active_is_cast_to_boolean(): void
    {
        $referrer = Referrer::factory()->create(['is_active' => 1]);

        $this->assertIsBool($referrer->fresh()->is_active);
        $this->assertTrue($referrer->fresh()->is_active);
    }

    public function test_kode_must_be_unique(): void
    {
        Referrer::factory()->create(['kode' => 'REF-DUPLICATE']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Referrer::factory()->create(['kode' => 'REF-DUPLICATE']);
    }
}
