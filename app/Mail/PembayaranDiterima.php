<?php

namespace App\Mail;

use App\Models\Pendaftaran;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PembayaranDiterima extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Pendaftaran $pendaftaran,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pembayaran Diterima — '.$this->pendaftaran->nomor_pendaftaran,
        );
    }

    public function content(): Content
    {
        $this->pendaftaran->loadMissing([
            'user',
            'tahun',
            'jalur',
            'prodiPilihan.prodi',
            'prodiPilihan.kelas',
        ]);

        return new Content(
            view: 'emails.pembayaran-diterima',
            with: [
                'pendaftaran' => $this->pendaftaran,
                'detailUrl' => route('mahasiswa.pendaftaran.show', $this->pendaftaran),
            ],
        );
    }
}
