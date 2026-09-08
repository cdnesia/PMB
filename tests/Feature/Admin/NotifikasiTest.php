<?php

namespace Tests\Feature\Admin;

use App\Models\Jalur;
use App\Models\Pendaftaran;
use App\Models\TahunPenerimaan;
use App\Models\User;
use App\Services\AdminNotificationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifikasiTest extends TestCase
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

    private static int $counter = 0;

    private function pendaftaran(): Pendaftaran
    {
        $n = ++self::$counter;

        $tahun = TahunPenerimaan::create(['kode' => "2027/2028-{$n}", 'nama' => 'Tahun 2027/2028', 'status' => 'aktif']);
        $jalur = Jalur::create(['kode' => "REGULER-{$n}", 'nama' => 'Jalur Reguler']);
        $mahasiswa = User::factory()->create();

        return Pendaftaran::forceCreate([
            'no_urut' => $n,
            'user_id' => $mahasiswa->id,
            'tahun_id' => $tahun->id,
            'jalur_id' => $jalur->id,
            'nomor_pendaftaran' => "PMB-2027-{$n}",
            'status' => 'menunggu_pembayaran',
            'status_pembayaran' => 'belum_bayar',
        ]);
    }

    public function test_pendaftar_baru_notifies_every_admin_and_admin_pmb(): void
    {
        $superAdmin = User::where('email', 'admin@pmb.test')->firstOrFail();
        $adminPmb = User::where('email', 'adminpmb@pmb.test')->firstOrFail();

        app(AdminNotificationService::class)->pendaftarBaru($this->pendaftaran());

        $this->assertSame(1, $superAdmin->notifications()->count());
        $this->assertSame(1, $adminPmb->notifications()->count());

        $notif = $superAdmin->notifications()->first();
        $this->assertSame('Pendaftar baru', $notif->data['judul']);
        $this->assertNotEmpty($notif->data['url']);
    }

    public function test_mark_one_notification_as_read_via_ajax(): void
    {
        $admin = $this->admin();
        app(AdminNotificationService::class)->pendaftarBaru($this->pendaftaran());
        $notif = $admin->notifications()->firstOrFail();

        $this->assertNull($notif->read_at);

        $response = $this->actingAs($admin)->postJson(route('admin.notifikasi.baca', $notif->id));

        $response->assertOk();
        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_mark_all_notifications_as_read_via_ajax(): void
    {
        $admin = $this->admin();
        app(AdminNotificationService::class)->pendaftarBaru($this->pendaftaran());
        app(AdminNotificationService::class)->pendaftarBaru($this->pendaftaran());

        $this->assertSame(2, $admin->unreadNotifications()->count());

        $response = $this->actingAs($admin)->postJson(route('admin.notifikasi.baca-semua'));

        $response->assertOk();
        $this->assertSame(0, $admin->unreadNotifications()->count());
    }

    public function test_admin_cannot_mark_another_users_notification(): void
    {
        $admin = $this->admin();
        $otherAdmin = User::where('email', 'adminpmb@pmb.test')->firstOrFail();
        app(AdminNotificationService::class)->pendaftarBaru($this->pendaftaran());

        $otherNotif = $otherAdmin->notifications()->firstOrFail();

        $response = $this->actingAs($admin)->postJson(route('admin.notifikasi.baca', $otherNotif->id));

        $response->assertNotFound();
    }
}
