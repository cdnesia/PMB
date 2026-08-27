<?php

namespace App\Models;

use App\Models\Concerns\PunyaNamaTerjemahan;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promo extends Model
{
    use HasUuids;
    use PunyaNamaTerjemahan;

    protected $table = 'promo';

    protected $fillable = [
        'kode',
        'nama',
        'jenis',
        'tipe',
        'nilai',
        'maks_potongan',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'is_global',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'decimal:2',
            'maks_potongan' => 'decimal:2',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'is_active' => 'boolean',
            'is_global' => 'boolean',
        ];
    }

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(Pendaftaran::class, 'promo_id');
    }

    public function ketentuan(): HasMany
    {
        return $this->hasMany(PromoKetentuan::class, 'promo_id');
    }

    /**
     * Promo masih aktif & dalam rentang tanggal berlakunya.
     */
    public function isBerlaku(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $today = now()->toDateString();

        if ($this->tanggal_mulai && $this->tanggal_mulai->toDateString() > $today) {
            return false;
        }

        if ($this->tanggal_selesai && $this->tanggal_selesai->toDateString() < $today) {
            return false;
        }

        return true;
    }

    /**
     * Apakah promo berlaku untuk jenis biaya tertentu (pendaftaran / spp).
     */
    public function berlakuUntuk(string $jenis): bool
    {
        return $this->jenis === 'semua' || $this->jenis === $jenis;
    }

    /**
     * Apakah promo cocok untuk kombinasi jalur + prodi + kelas tertentu.
     * Promo global selalu cocok; selain itu harus ada ketentuan yang cocok.
     */
    public function cocokUntuk(?string $jalurId, ?string $prodiId, ?string $kelasId): bool
    {
        if ($this->is_global) {
            return true;
        }

        return $this->ketentuan->contains(function ($k) use ($jalurId, $prodiId, $kelasId) {
            return $k->jalur_id === $jalurId
                && $k->prodi_id === $prodiId
                && $k->kelas_id === $kelasId;
        });
    }

    /**
     * Hitung nominal potongan dari suatu nilai dasar biaya.
     */
    public function hitungPotongan(float $dasar): float
    {
        $dasar = max(0, $dasar);

        if ($this->tipe === 'nominal') {
            return min((float) $this->nilai, $dasar);
        }

        $potongan = $dasar * ((float) $this->nilai / 100);

        if ($this->maks_potongan !== null && (float) $this->maks_potongan >= 0) {
            $potongan = min($potongan, (float) $this->maks_potongan);
        }

        return min($potongan, $dasar);
    }

    /**
     * Label potongan yang mudah dibaca (mis. "50%" atau "Rp 500.000").
     */
    public function labelPotongan(): string
    {
        if ($this->tipe === 'nominal') {
            return 'Rp '.number_format((float) $this->nilai, 0, ',', '.');
        }

        $label = rtrim(rtrim(number_format((float) $this->nilai, 2, ',', '.'), '0'), ',').'%';

        if ($this->maks_potongan !== null && (float) $this->maks_potongan > 0) {
            $label .= ' (maks Rp '.number_format((float) $this->maks_potongan, 0, ',', '.').')';
        }

        return $label;
    }

    /**
     * Label jenis potongan (biaya yang dipotong).
     */
    public function labelJenis(): string
    {
        return match ($this->jenis) {
            'spp' => 'Biaya SPP',
            'semua' => 'Pendaftaran & SPP',
            default => 'Biaya Pendaftaran',
        };
    }
}
