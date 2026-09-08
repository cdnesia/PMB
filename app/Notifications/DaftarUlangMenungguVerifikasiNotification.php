<?php

namespace App\Notifications;

use App\Models\Pendaftaran;
use Illuminate\Notifications\Notification;

class DaftarUlangMenungguVerifikasiNotification extends Notification
{
    public function __construct(private readonly Pendaftaran $pendaftaran)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $nominal = $this->pendaftaran->daftarUlang?->nominal;

        return [
            'judul' => 'Daftar ulang menunggu verifikasi',
            'pesan' => ($this->pendaftaran->user?->name ?? 'Seseorang').' mengirim bukti pembayaran daftar ulang'
                .($nominal !== null ? ' sebesar Rp '.number_format((float) $nominal, 0, ',', '.') : '').'.',
            'url' => route('admin.pendaftar.show', $this->pendaftaran),
            'ikon' => 'credit-card',
        ];
    }
}
