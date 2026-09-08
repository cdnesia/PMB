<?php

namespace App\Http\Controllers;

use App\Models\DaftarUlang;
use App\Models\DokumenPendaftar;
use App\Models\PembayaranPendaftaran;
use App\Models\Pendaftaran;
use App\Models\PendaftaranSyarat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DokumenController extends Controller
{
    public function persyaratan(DokumenPendaftar $dokumen): StreamedResponse
    {
        $this->authorizeAkses($dokumen->pendaftaran);

        return $this->stream($dokumen->file_path);
    }

    public function syarat(PendaftaranSyarat $syarat): StreamedResponse
    {
        $this->authorizeAkses($syarat->pendaftaran);

        return $this->stream($syarat->file_path);
    }

    public function pembayaran(PembayaranPendaftaran $pembayaran): StreamedResponse
    {
        $this->authorizeAkses($pembayaran->pendaftaran);

        return $this->stream($pembayaran->bukti_bayar);
    }

    public function daftarUlang(DaftarUlang $daftarUlang): StreamedResponse
    {
        $this->authorizeAkses($daftarUlang->pendaftaran);

        return $this->stream($daftarUlang->bukti_bayar);
    }

    private function authorizeAkses(Pendaftaran $pendaftaran): void
    {
        $user = Auth::user();

        abort_unless(
            $pendaftaran->user_id === $user->id || $user->hasAnyRole(['super-admin', 'admin-pmb']),
            403
        );
    }

    private function stream(?string $path): StreamedResponse
    {
        abort_if($path === null || ! Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
