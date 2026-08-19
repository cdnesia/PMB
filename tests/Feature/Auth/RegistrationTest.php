<?php

namespace Tests\Feature\Auth;

use App\Models\Referrer;
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

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('mahasiswa.dashboard', absolute: false));
    }

    public function test_registering_with_a_valid_referral_code_links_the_referrer(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $referrer = Referrer::where('kode', 'REF-MITRA')->firstOrFail();

        $response = $this->post('/register', [
            'name' => 'Referred User',
            'email' => 'referred@example.com',
            'phone' => '081234567891',
            'password' => 'password',
            'password_confirmation' => 'password',
            'kode_referral' => 'ref-mitra',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('mahasiswa.dashboard', absolute: false));

        $user = User::where('email', 'referred@example.com')->firstOrFail();
        $this->assertSame($referrer->id, $user->referrer_id);
    }

    public function test_registering_with_an_invalid_referral_code_fails_validation(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'phone' => '081234567892',
            'password' => 'password',
            'password_confirmation' => 'password',
            'kode_referral' => 'TIDAK-ADA',
        ]);

        $response->assertSessionHasErrors('kode_referral');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test2@example.com']);
    }

    public function test_registering_with_an_inactive_referral_code_fails_validation(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        Referrer::factory()->inactive()->create(['kode' => 'REF-NONAKTIF']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test3@example.com',
            'phone' => '081234567893',
            'password' => 'password',
            'password_confirmation' => 'password',
            'kode_referral' => 'REF-NONAKTIF',
        ]);

        $response->assertSessionHasErrors('kode_referral');
        $this->assertGuest();
    }

    public function test_registering_without_a_referral_code_leaves_referrer_null(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->post('/register', [
            'name' => 'No Referral User',
            'email' => 'noreferral@example.com',
            'phone' => '081234567894',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'noreferral@example.com')->firstOrFail();
        $this->assertNull($user->referrer_id);
    }
}
