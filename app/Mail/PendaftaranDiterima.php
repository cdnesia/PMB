<?php

namespace App\Mail;

use App\Models\Pendaftaran;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PendaftaranDiterima extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Pendaftaran $pendaftaran,
    ) {
    }

    /**
     * Subjek email yang tampil di kotak masuk penerima.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran Diterima — '.$this->pendaftaran->nomor_pendaftaran,
        );
    }

    /**
     * Data yang diteruskan ke template email.
     */
    public function content(): Content
    {
        $this->pendaftaran->loadMissing([
            'user',
            'tahun',
            'gelombang',
            'jalur',
            'prodiPilihan.prodi',
            'prodiPilihan.kelas',
        ]);

        return new Content(
            view: 'emails.pendaftaran-diterima',
            with: [
                'pendaftaran' => $this->pendaftaran,
                'detailUrl' => route('mahasiswa.pendaftaran.show', $this->pendaftaran),
            ],
        );
    }
}
