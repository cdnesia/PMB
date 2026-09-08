<?php

namespace App\Notifications;

use App\Models\Pendaftaran;
use Illuminate\Notifications\Notification;

class PendaftarBaruNotification extends Notification
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
        return [
            'judul' => 'Pendaftar baru',
            'pesan' => ($this->pendaftaran->user?->name ?? 'Seseorang').' mendaftar melalui jalur '.($this->pendaftaran->jalur?->nama ?? '-').'.',
            'url' => route('admin.pendaftar.show', $this->pendaftaran),
            'ikon' => 'user',
        ];
    }
}
