<?php

namespace Tests\Feature\Auth;

use App\Models\Referrer;
use App\Models\SumberInformasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $sumber = SumberInformasi::create(['kode' => 'TEMAN', 'nama' => 'Teman']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
            'sumber_informasi_id' => $sumber->id,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('mahasiswa.dashboard', absolute: false));
    }

    public function test_registering_with_a_valid_referral_code_links_the_referrer(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $referrer = Referrer::where('kode', 'REF-MITRA')->firstOrFail();
        $sumber = SumberInformasi::create(['kode' => 'TEMAN', 'nama' => 'Teman']);

        $response = $this->post('/register', [
            'name' => 'Referred User',
            'email' => 'referred@example.com',
            'phone' => '081234567891',
            'password' => 'password',
            'password_confirmation' => 'password',
            'kode_referral' => 'ref-mitra',
            'sumber_informasi_id' => $sumber->id,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('mahasiswa.dashboard', absolute: false));

        $user = User::where('email', 'referred@example.com')->firstOrFail();
        $this->assertSame($referrer->id, $user->referrer_id);
    }

    public function test_registering_with_an_invalid_referral_code_fails_validation(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $sumber = SumberInformasi::create(['kode' => 'TEMAN', 'nama' => 'Teman']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'phone' => '081234567892',
            'password' => 'password',
            'password_confirmation' => 'password',
            'kode_referral' => 'TIDAK-ADA',
            'sumber_informasi_id' => $sumber->id,
        ]);

        $response->assertSessionHasErrors('kode_referral');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test2@example.com']);
    }

    public function test_registering_with_an_inactive_referral_code_fails_validation(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        Referrer::factory()->inactive()->create(['kode' => 'REF-NONAKTIF']);
        $sumber = SumberInformasi::create(['kode' => 'TEMAN', 'nama' => 'Teman']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test3@example.com',
            'phone' => '081234567893',
            'password' => 'password',
            'password_confirmation' => 'password',
            'kode_referral' => 'REF-NONAKTIF',
            'sumber_informasi_id' => $sumber->id,
        ]);

        $response->assertSessionHasErrors('kode_referral');
        $this->assertGuest();
    }

    public function test_registering_without_a_referral_code_leaves_referrer_null(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $sumber = SumberInformasi::create(['kode' => 'TEMAN', 'nama' => 'Teman']);

        $this->post('/register', [
            'name' => 'No Referral User',
            'email' => 'noreferral@example.com',
            'phone' => '081234567894',
            'password' => 'password',
            'password_confirmation' => 'password',
            'sumber_informasi_id' => $sumber->id,
        ]);

        $user = User::where('email', 'noreferral@example.com')->firstOrFail();
        $this->assertNull($user->referrer_id);
    }

    public function test_failed_registration_keeps_all_input_except_password(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $sumber = SumberInformasi::create(['kode' => 'TEMAN', 'nama' => 'Teman']);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'bukan-email-valid',
            'phone' => '081234567895',
            'password' => 'password',
            'password_confirmation' => 'password',
            'kode_referral' => 'TIDAK-ADA',
            'sumber_informasi_id' => $sumber->id,
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHasInput('name', 'Test User');
        $response->assertSessionHasInput('email', 'bukan-email-valid');
        $response->assertSessionHasInput('phone', '081234567895');
        $response->assertSessionHasInput('kode_referral', 'TIDAK-ADA');
        $response->assertSessionHasInput('sumber_informasi_id', $sumber->id);
        $response->assertSessionMissing('_old_input.password');
        $response->assertSessionMissing('_old_input.password_confirmation');
    }
}
