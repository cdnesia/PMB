<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DokumenPendaftar;
use App\Models\Jalur;
use App\Models\Pendaftaran;
use App\Models\PendaftaranProdi;
use App\Models\Prodi;
use App\Services\PendaftaranNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PendaftarController extends Controller
{
    private const STATUS_LIST = [
        'draft',
        'menunggu_pembayaran',
        'lunas',
        'terverifikasi',
        'lolos',
        'cadangan',
        'tidak_lolos',
        'daftar_ulang',
        'mahasiswa_baru',
        'ditolak',
    ];

    public function index(Request $request): View
    {
        $pendaftaran = Pendaftaran::with(['user', 'tahun', 'jalur', 'prodiPilihan.prodi', 'prodiPilihan.kelas'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('status_pembayaran'), fn ($q) => $q->where('status_pembayaran', $request->status_pembayaran))
            ->when($request->filled('jalur_id'), fn ($q) => $q->where('jalur_id', $request->jalur_id))
            ->when($request->filled('prodi_id'), fn ($q) => $q->whereHas('prodiPilihan', fn ($qq) => $qq->where('prodi_id', $request->prodi_id)))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->q;
                $q->where(function ($qq) use ($term) {
                    $qq->where('nomor_pendaftaran', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $jalurList = Jalur::orderBy('urutan')->get();
        $prodiList = Prodi::orderBy('jenjang')->orderBy('nama')->get();

        return view('admin.pendaftar.index', compact('pendaftaran', 'jalurList', 'prodiList'));
    }

    public function show(Pendaftaran $pendaftaran): View
    {
        $pendaftaran->load([
            'user',
            'tahun',
            'jalur',
            'gelombang',
            'promo',
            'prodiPilihan.prodi',
            'prodiPilihan.kelas',
            'dokumen.dokumenPersyaratan',
            'pendaftar',
            'daftarUlang',
            'syaratJawaban.syarat',
            'cbtSesi',
        ]);

        return view('admin.pendaftar.show', compact('pendaftaran'));
    }

    public function updatePembayaran(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $request->validate([
            'status_pembayaran' => 'required|in:belum_bayar,lunas',
        ]);

        $pendaftaran->update(['status_pembayaran' => $request->status_pembayaran]);

        // Jika lunas dan status masih awal, majukan status
        if ($request->status_pembayaran === 'lunas' && in_array($pendaftaran->status, ['draft', 'menunggu_pembayaran'])) {
            $pendaftaran->update(['status' => 'lunas']);
        }

        // Kirim notifikasi email saat pembayaran pendaftaran dikonfirmasi lunas.
        if ($request->status_pembayaran === 'lunas') {
            app(PendaftaranNotificationService::class)->sendPembayaranDiterima($pendaftaran);
        }

        return back()->with('success', 'Status pembayaran diperbarui.');
    }

    public function verifikasiDokumen(Request $request, DokumenPendaftar $dokumen): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:terverifikasi,ditolak,menunggu',
        ]);

        $dokumen->update(['status' => $request->status]);

        $pesan = match ($request->status) {
            'terverifikasi' => 'diverifikasi',
            'ditolak' => 'ditolak',
            default => 'dikembalikan ke menunggu',
        };

        return back()->with('success', 'Dokumen "'.$dokumen->nama.'" berhasil '.$pesan.'.');
    }

    public function verifikasiBerkas(Pendaftaran $pendaftaran): RedirectResponse
    {
        // Semua dokumen wajib sudah terverifikasi baru boleh lanjut
        $pending = $pendaftaran->dokumen()->where('status', '!=', 'terverifikasi')->exists();

        if ($pending) {
            return back()->withErrors(['berkas' => 'Masih ada dokumen yang belum terverifikasi.']);
        }

        $pendaftaran->update(['status' => 'terverifikasi']);

        return back()->with('success', 'Berkas ditandai lengkap & terverifikasi.');
    }

    public function inputNilai(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $request->validate([
            'nilai_seleksi' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        $pendaftaran->update([
            'nilai_seleksi' => $request->filled('nilai_seleksi') ? $request->nilai_seleksi : null,
        ]);

        return back()->with('success', 'Nilai seleksi berhasil disimpan.');
    }

    public function updateStatus(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:'.implode(',', self::STATUS_LIST),
            'prodi_status' => 'nullable|array',
            'prodi_status.*' => 'in:lolos,cadangan,tidak_lolos',
        ]);

        // Perbarui status kelulusan tiap pilihan prodi
        foreach ($request->input('prodi_status', []) as $id => $status) {
            PendaftaranProdi::where('id', $id)
                ->where('pendaftaran_id', $pendaftaran->id)
                ->update(['status' => $status]);
        }

        $pendaftaran->update(['status' => $request->status]);

        return back()->with('success', 'Status pendaftaran diperbarui menjadi "'.str_replace('_', ' ', $request->status).'".');
    }

    public function resetPassword(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $pendaftaran->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password pendaftar "'.$pendaftaran->user->name.'" berhasil direset.');
    }

    /**
     * Verifikasi pembayaran daftar ulang (SPP) oleh bendahara/panitia.
     */
    public function verifikasiDaftarUlang(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:lunas,ditolak',
        ]);

        $daftarUlang = $pendaftaran->daftarUlang;

        if (! $daftarUlang) {
            return back()->withErrors(['daftar_ulang' => 'Pendaftar belum mengirim bukti pembayaran daftar ulang.']);
        }

        $daftarUlang->update([
            'status' => $request->status,
            'catatan' => $request->input('catatan'),
        ]);

        if ($request->status === 'lunas') {
            $pendaftaran->update(['status' => 'mahasiswa_baru']);

            // Kirim email pemberitahuan resmi menjadi mahasiswa baru.
            app(PendaftaranNotificationService::class)->sendDaftarUlangDiterima($pendaftaran);

            return back()->with('success', 'Pembayaran daftar ulang dikonfirmasi. Pendaftar resmi menjadi mahasiswa baru.');
        }

        return back()->with('success', 'Pembayaran daftar ulang ditolak. Pendaftar dapat mengunggah ulang bukti.');
    }
}
