<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pendaftaran extends Model
{
    use HasUuids;

    protected $table = 'pendaftaran';

    protected $fillable = [
        'user_id',
        'tahun_id',
        'jalur_id',
        'gelombang_id',
        'promo_id',
        'referrer_id',
        'nomor_pendaftaran',
        'status',
        'status_pembayaran',
        'nilai_seleksi',
        'cbt_menit_tambahan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'nilai_seleksi' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tahun(): BelongsTo
    {
        return $this->belongsTo(TahunPenerimaan::class, 'tahun_id');
    }

    public function jalur(): BelongsTo
    {
        return $this->belongsTo(Jalur::class, 'jalur_id');
    }

    public function gelombang(): BelongsTo
    {
        return $this->belongsTo(Gelombang::class, 'gelombang_id');
    }

    public function promo(): BelongsTo
    {
        return $this->belongsTo(Promo::class, 'promo_id');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Referrer::class, 'referrer_id');
    }

    public function prodiPilihan(): HasMany
    {
        return $this->hasMany(PendaftaranProdi::class, 'pendaftaran_id')->orderBy('urutan');
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(DokumenPendaftar::class, 'pendaftaran_id');
    }

    public function pendaftar(): HasOne
    {
        return $this->hasOne(Pendaftar::class, 'pendaftaran_id');
    }

    public function daftarUlang(): HasOne
    {
        return $this->hasOne(DaftarUlang::class, 'pendaftaran_id');
    }

    public function syaratJawaban(): HasMany
    {
        return $this->hasMany(PendaftaranSyarat::class, 'pendaftaran_id');
    }

    public function cbtSesi(): HasMany
    {
        return $this->hasMany(CbtSesi::class, 'pendaftaran_id');
    }

    /**
     * Mahasiswa dinyatakan lolos jika minimal satu pilihan prodi berstatus
     * "lolos", atau status pendaftaran sudah maju melewati seleksi.
     */
    public function isLolos(): bool
    {
        if (in_array($this->status, ['lolos', 'daftar_ulang', 'mahasiswa_baru'], true)) {
            return true;
        }

        return $this->prodiPilihan->contains(fn ($p) => $p->status === 'lolos');
    }

    /**
     * Biaya pendaftaran dasar sesuai kombinasi jalur + kelas yang dipilih.
     * Kelas yang menjadi acuan adalah kelas pada pilihan prodi pertama.
     * Bila kombinasi jalur+kelas belum dikonfigurasi, gunakan biaya default jalur.
     */
    public function biayaPendaftaranAwal(): float
    {
        $kelasId = $this->prodiPilihan->first()?->kelas_id;

        if ($kelasId && $this->jalur) {
            $biaya = $this->jalur->kelasBiaya()
                ->where('kelas_id', $kelasId)
                ->value('biaya_pendaftaran');

            if ($biaya !== null) {
                return (float) $biaya;
            }
        }

        return (float) ($this->jalur?->biaya_pendaftaran ?? 0);
    }

    /**
     * Total potongan promo untuk biaya pendaftaran (0 jika tidak pakai promo).
     */
    public function potonganPendaftaran(): float
    {
        $dasar = $this->biayaPendaftaranAwal();

        if ($dasar <= 0) {
            return 0;
        }

        if ($this->promo && $this->promo->isBerlaku() && $this->promo->berlakuUntuk('pendaftaran')) {
            return $this->promo->hitungPotongan($dasar);
        }

        return 0;
    }

    /**
     * Biaya pendaftaran setelah potongan promo.
     */
    public function biayaPendaftaranAkhir(): float
    {
        return max(0, $this->biayaPendaftaranAwal() - $this->potonganPendaftaran());
    }
}
