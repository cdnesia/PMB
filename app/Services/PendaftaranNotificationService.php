<?php

namespace App\Services;

use App\Mail\DaftarUlangDiterima;
use App\Mail\PembayaranDiterima;
use App\Mail\PendaftaranDiterima;
use App\Mail\SelamatDatang;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PendaftaranNotificationService
{
    /**
     * Email selamat datang setelah akun pendaftar dibuat.
     */
    public function sendSelamatDatang(User $user): bool
    {
        if (! $this->isValidEmail($user->email)) {
            return false;
        }

        return $this->send(new SelamatDatang($user), $user->email, $user->name);
    }

    /**
     * Email konfirmasi bahwa formulir pendaftaran berhasil diterima.
     */
    public function sendPendaftaranDiterima(Pendaftaran $pendaftaran): bool
    {
        $user = $pendaftaran->user;

        if (! $user || ! $this->isValidEmail($user->email)) {
            return false;
        }

        return $this->send(new PendaftaranDiterima($pendaftaran), $user->email, $user->name);
    }

    /**
     * Email konfirmasi pembayaran biaya pendaftaran (status lunas).
     */
    public function sendPembayaranDiterima(Pendaftaran $pendaftaran): bool
    {
        $user = $pendaftaran->user;

        if (! $user || ! $this->isValidEmail($user->email)) {
            return false;
        }

        return $this->send(new PembayaranDiterima($pendaftaran), $user->email, $user->name);
    }

    /**
     * Email konfirmasi pembayaran daftar ulang (SPP) — pendaftar resmi mahasiswa baru.
     */
    public function sendDaftarUlangDiterima(Pendaftaran $pendaftaran): bool
    {
        $user = $pendaftaran->user;

        if (! $user || ! $this->isValidEmail($user->email)) {
            return false;
        }

        return $this->send(new DaftarUlangDiterima($pendaftaran), $user->email, $user->name);
    }

    /**
     * Kirim email. Kegagalan pengiriman TIDAK boleh menggagalkan proses utama,
     * sehingga exception ditangkap dan hanya dicatat ke log.
     */
    private function send($mailable, string $email, ?string $name = null): bool
    {
        try {
            Mail::to($email, $name)->send($mailable);

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }

    private function isValidEmail(?string $email): bool
    {
        return $email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
