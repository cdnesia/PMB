<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SelamatDatang extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Selamat Datang di PMB — '.$this->user->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.selamat-datang',
            with: [
                'user' => $this->user,
                'pendaftaranUrl' => route('mahasiswa.pendaftaran.create'),
                'dashboardUrl' => route('mahasiswa.dashboard'),
            ],
        );
    }
}
