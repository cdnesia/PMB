<?php

namespace App\Services;

use App\Models\CbtJadwal;
use App\Models\CbtSesi;
use App\Models\CbtSoal;
use App\Models\Pendaftaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CbtService
{
    /**
     * Cari jadwal CBT yang berlaku untuk pendaftaran ini (jalur cocok, gelombang cocok/umum,
     * aktif, dan sedang dalam jendela waktu pelaksanaan). Jadwal yang secara eksplisit
     * menargetkan salah satu prodi pilihan peserta (mis. "Jadwal CBT Anestesi")
     * diprioritaskan; jika tidak ada, jatuh ke jadwal umum (tanpa target prodi).
     */
    public function jadwalBerlaku(Pendaftaran $pendaftaran): ?CbtJadwal
    {
        if (! $pendaftaran->jalur?->requires_cbt) {
            return null;
        }

        $query = CbtJadwal::where('jalur_id', $pendaftaran->jalur_id)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('gelombang_id')->orWhere('gelombang_id', $pendaftaran->gelombang_id))
            ->where('waktu_mulai', '<=', now())
            ->where('waktu_selesai', '>=', now());

        $prodiIds = $pendaftaran->prodiPilihan->pluck('prodi_id');

        if ($prodiIds->isNotEmpty()) {
            $spesifik = (clone $query)->whereIn('prodi_id', $prodiIds)->orderByDesc('waktu_mulai')->first();
            if ($spesifik) {
                return $spesifik;
            }
        }

        return (clone $query)->whereNull('prodi_id')->orderByDesc('waktu_mulai')->first();
    }

    /**
     * Mulai sesi ujian baru untuk pendaftaran pada jadwal tertentu.
     * Menolak jika sudah pernah mengerjakan (satu kesempatan per jadwal).
     */
    public function mulai(Pendaftaran $pendaftaran, CbtJadwal $jadwal): CbtSesi
    {
        $sudahAda = CbtSesi::where('cbt_jadwal_id', $jadwal->id)
            ->where('pendaftaran_id', $pendaftaran->id)
            ->exists();

        if ($sudahAda) {
            throw ValidationException::withMessages([
                'cbt' => 'Anda sudah pernah memulai ujian ini.',
            ]);
        }

        $soalIds = $this->susunSoal($jadwal);

        $mulai = now();
        $batasDurasi = $mulai->copy()->addMinutes($jadwal->durasi_menit + $pendaftaran->cbt_menit_tambahan);
        $deadline = $batasDurasi->lessThan($jadwal->waktu_selesai) ? $batasDurasi : $jadwal->waktu_selesai;

        return CbtSesi::create([
            'cbt_jadwal_id' => $jadwal->id,
            'pendaftaran_id' => $pendaftaran->id,
            'status' => 'berlangsung',
            'soal_urutan' => $soalIds,
            'started_at' => $mulai,
            'deadline_at' => $deadline,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);
    }

    /**
     * Susun daftar id soal untuk satu peserta. Prodi target ditentukan oleh JADWAL sendiri
     * (`cbt_jadwal.prodi_id`), bukan ditebak dari pilihan prodi pendaftar — jadwal umum
     * (prodi_id null) hanya mengambil soal umum, jadwal yang menargetkan satu prodi
     * (mis. "Jadwal CBT Anestesi") mengambil soal umum + soal khusus prodi tsb sesuai
     * dua kuota terpisah & aditif per kategori (`jumlah` + `jumlah_prodi`).
     */
    private function susunSoal(CbtJadwal $jadwal): array
    {
        $komposisi = $jadwal->komposisi;

        if ($komposisi->isEmpty()) {
            throw ValidationException::withMessages([
                'cbt' => 'Komposisi soal untuk jadwal ini belum diatur. Hubungi panitia.',
            ]);
        }

        $prodiId = $jadwal->prodi_id;
        $soalIds = collect();

        foreach ($komposisi as $item) {
            $umum = CbtSoal::where('is_active', true)
                ->where(fn ($q) => $q->whereNull('jalur_id')->orWhere('jalur_id', $jadwal->jalur_id))
                ->whereNull('prodi_id')
                ->where('kategori', $item->kategori)
                ->inRandomOrder()
                ->limit($item->jumlah)
                ->pluck('id');

            if ($umum->count() < $item->jumlah) {
                throw ValidationException::withMessages([
                    'cbt' => "Bank soal umum kategori \"{$item->kategori}\" belum mencukupi ({$umum->count()}/{$item->jumlah}). Hubungi panitia.",
                ]);
            }

            $soalIds = $soalIds->merge($umum);

            if ($item->jumlah_prodi > 0 && $prodiId) {
                $khusus = CbtSoal::where('is_active', true)
                    ->where(fn ($q) => $q->whereNull('jalur_id')->orWhere('jalur_id', $jadwal->jalur_id))
                    ->where('prodi_id', $prodiId)
                    ->where('kategori', $item->kategori)
                    ->inRandomOrder()
                    ->limit($item->jumlah_prodi)
                    ->pluck('id');

                if ($khusus->count() < $item->jumlah_prodi) {
                    throw ValidationException::withMessages([
                        'cbt' => "Bank soal khusus prodi kategori \"{$item->kategori}\" belum mencukupi ({$khusus->count()}/{$item->jumlah_prodi}). Hubungi panitia.",
                    ]);
                }

                $soalIds = $soalIds->merge($khusus);
            }
        }

        return $soalIds->shuffle()->values()->all();
    }

    /**
     * Simpan/perbarui satu jawaban (autosave). Ditolak bila sesi sudah selesai atau kedaluwarsa.
     */
    public function simpanJawaban(CbtSesi $sesi, CbtSoal $soal, ?string $jawaban, bool $raguRagu): void
    {
        if ($sesi->sudahSelesai() || $sesi->sudahLewatDeadline()) {
            throw ValidationException::withMessages([
                'cbt' => 'Sesi ujian sudah berakhir.',
            ]);
        }

        $sesi->jawaban()->updateOrCreate(
            ['cbt_soal_id' => $soal->id],
            [
                'jawaban' => $jawaban,
                'ragu_ragu' => $raguRagu,
                'answered_at' => now(),
            ]
        );
    }

    public function catatPelanggaran(CbtSesi $sesi, string $jenis, ?string $keterangan = null): void
    {
        if ($sesi->sudahSelesai()) {
            return;
        }

        $sesi->pelanggaran()->create([
            'jenis' => $jenis,
            'keterangan' => $keterangan,
            'terjadi_pada' => now(),
        ]);

        $sesi->increment('jumlah_pelanggaran');
    }

    /**
     * Tutup & nilai sesi ujian. Skor dihitung dari total bobot soal yang dijawab benar,
     * dinormalisasi ke skala 0-100, lalu didorong ke Pendaftaran.nilai_seleksi.
     */
    public function finalisasi(CbtSesi $sesi, string $reason): CbtSesi
    {
        return DB::transaction(function () use ($sesi, $reason) {
            $sesi = CbtSesi::whereKey($sesi->id)->lockForUpdate()->firstOrFail();

            if ($sesi->sudahSelesai()) {
                return $sesi;
            }

            $soalList = CbtSoal::whereIn('id', $sesi->soal_urutan)->get()->keyBy('id');
            $jawabanList = $sesi->jawaban()->get()->keyBy('cbt_soal_id');

            $totalBobot = 0;
            $bobotBenar = 0;
            $benar = 0;
            $salah = 0;
            $kosong = 0;

            foreach ($soalList as $soalId => $soal) {
                $totalBobot += (float) $soal->bobot;
                $jawaban = $jawabanList->get($soalId);

                if (! $jawaban || blank($jawaban->jawaban)) {
                    $kosong++;

                    continue;
                }

                $isBenar = strtolower($jawaban->jawaban) === strtolower($soal->kunci_jawaban);
                $jawaban->update(['is_benar' => $isBenar]);

                if ($isBenar) {
                    $benar++;
                    $bobotBenar += (float) $soal->bobot;
                } else {
                    $salah++;
                }
            }

            $skor = $totalBobot > 0 ? round(($bobotBenar / $totalBobot) * 100, 2) : 0;

            $sesi->update([
                'status' => 'selesai',
                'skor' => $skor,
                'jumlah_benar' => $benar,
                'jumlah_salah' => $salah,
                'jumlah_kosong' => $kosong,
                'finished_at' => now(),
                'finish_reason' => $reason,
            ]);

            $sesi->pendaftaran->update(['nilai_seleksi' => $skor]);

            return $sesi;
        });
    }

    /**
     * Tutup paksa semua sesi yang masih "berlangsung" tapi sudah lewat deadline
     * (peserta menutup browser tanpa submit). Dipanggil dari scheduler.
     */
    public function tutupSesiKedaluwarsa(): int
    {
        $sesiKedaluwarsa = CbtSesi::where('status', 'berlangsung')
            ->where('deadline_at', '<', now())
            ->get();

        foreach ($sesiKedaluwarsa as $sesi) {
            $this->finalisasi($sesi, 'auto_timeout');
        }

        return $sesiKedaluwarsa->count();
    }
}
