<?php

namespace Tests\Feature;

use App\Models\Referrer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_search_active_referral_codes(): void
    {
        Referrer::factory()->mitra()->create(['kode' => 'REF-SEKOLAHKU', 'nama_instansi' => 'SMA Sekolahku']);

        $response = $this->getJson('/referral/search?q=SEKOLAH');

        $response->assertOk();
        $response->assertJsonFragment(['id' => 'REF-SEKOLAHKU']);
    }

    public function test_search_matches_by_institution_name_too(): void
    {
        Referrer::factory()->mitra()->create(['kode' => 'REF-ABC', 'nama_instansi' => 'SMA Negeri 5 Jambi']);

        $response = $this->getJson('/referral/search?q=Negeri 5');

        $response->assertOk();
        $response->assertJsonFragment(['id' => 'REF-ABC']);
    }

    public function test_search_matches_by_referrer_name_too(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);
        Referrer::factory()->karyawan()->create(['user_id' => $user->id, 'kode' => 'REF-BUDI']);

        $response = $this->getJson('/referral/search?q=Budi San');

        $response->assertOk();
        $response->assertJsonFragment(['id' => 'REF-BUDI']);
    }

    public function test_inactive_referrer_is_excluded_from_search(): void
    {
        Referrer::factory()->inactive()->create(['kode' => 'REF-NONAKTIF']);

        $response = $this->getJson('/referral/search?q=NONAKTIF');

        $response->assertOk();
        $response->assertJsonMissing(['id' => 'REF-NONAKTIF']);
    }

    public function test_empty_query_returns_no_results(): void
    {
        Referrer::factory()->mitra()->create(['kode' => 'REF-ABC']);

        $response = $this->getJson('/referral/search?q=');

        $response->assertOk();
        $response->assertJson(['results' => []]);
    }
}
