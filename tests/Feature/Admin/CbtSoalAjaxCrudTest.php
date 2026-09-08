<?php

namespace Tests\Feature\Admin;

use App\Models\CbtSoal;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbtSoalAjaxCrudTest extends TestCase
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

    public function test_store_via_ajax(): void
    {
        $response = $this->actingAs($this->admin())->postJson(route('admin.cbt-soal.store'), [
            'kategori' => 'Akademik',
            'pertanyaan' => 'Apa ibu kota Indonesia?',
            'pilihan_a' => 'Jakarta',
            'pilihan_b' => 'Bandung',
            'pilihan_c' => 'Surabaya',
            'pilihan_d' => 'Medan',
            'kunci_jawaban' => 'a',
            'bobot' => 1,
            'is_active' => '1',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('cbt_soal', ['kategori' => 'Akademik', 'kunci_jawaban' => 'a']);
    }

    public function test_store_with_kunci_e_but_blank_pilihan_e_returns_422(): void
    {
        $response = $this->actingAs($this->admin())->postJson(route('admin.cbt-soal.store'), [
            'kategori' => 'Akademik',
            'pertanyaan' => 'Soal tidak valid',
            'pilihan_a' => 'A',
            'pilihan_b' => 'B',
            'pilihan_c' => 'C',
            'pilihan_d' => 'D',
            'kunci_jawaban' => 'e',
            'bobot' => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['kunci_jawaban']);
    }

    public function test_destroy_via_ajax(): void
    {
        $soal = CbtSoal::create([
            'kategori' => 'Akademik',
            'pertanyaan' => 'Soal contoh',
            'pilihan_a' => 'A',
            'pilihan_b' => 'B',
            'pilihan_c' => 'C',
            'pilihan_d' => 'D',
            'kunci_jawaban' => 'a',
            'bobot' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin())->deleteJson(route('admin.cbt-soal.destroy', $soal));

        $response->assertOk();
        $this->assertDatabaseMissing('cbt_soal', ['id' => $soal->id]);
    }
}
