<?php

namespace App\Services;

use App\Models\Pendaftaran;
use App\Models\User;
use App\Notifications\DaftarUlangMenungguVerifikasiNotification;
use App\Notifications\PembayaranMenungguVerifikasiNotification;
use App\Notifications\PendaftarBaruNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Kirim notifikasi lonceng (database) ke seluruh admin (super-admin &
 * admin-pmb) untuk aktivitas yang perlu ditindaklanjuti panitia.
 */
class AdminNotificationService
{
    public function pendaftarBaru(Pendaftaran $pendaftaran): void
    {
        Notification::send($this->admins(), new PendaftarBaruNotification($pendaftaran));
    }

    public function pembayaranMenungguVerifikasi(Pendaftaran $pendaftaran): void
    {
        Notification::send($this->admins(), new PembayaranMenungguVerifikasiNotification($pendaftaran));
    }

    public function daftarUlangMenungguVerifikasi(Pendaftaran $pendaftaran): void
    {
        Notification::send($this->admins(), new DaftarUlangMenungguVerifikasiNotification($pendaftaran));
    }

    private function admins()
    {
        return User::role(['super-admin', 'admin-pmb'])->get();
    }
}
