<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtJadwal extends Model
{
    use HasUuids;

    protected $table = 'cbt_jadwal';

    protected $fillable = [
        'jalur_id',
        'gelombang_id',
        'prodi_id',
        'nama',
        'durasi_menit',
        'waktu_mulai',
        'waktu_selesai',
        'nilai_kelulusan_minimum',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'waktu_mulai' => 'datetime',
            'waktu_selesai' => 'datetime',
            'nilai_kelulusan_minimum' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function jalur(): BelongsTo
    {
        return $this->belongsTo(Jalur::class, 'jalur_id');
    }

    public function gelombang(): BelongsTo
    {
        return $this->belongsTo(Gelombang::class, 'gelombang_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function sesi(): HasMany
    {
        return $this->hasMany(CbtSesi::class, 'cbt_jadwal_id');
    }

    public function komposisi(): HasMany
    {
        return $this->hasMany(CbtJadwalKomposisi::class, 'cbt_jadwal_id');
    }

    public function sedangBerlangsung(): bool
    {
        return $this->is_active
            && now()->between($this->waktu_mulai, $this->waktu_selesai);
    }

    /**
     * Total soal umum (di luar tambahan khusus prodi) dari seluruh kategori komposisi —
     * jumlah pasti yang didapat setiap peserta.
     */
    public function totalSoalUmum(): int
    {
        return $this->komposisi->sum('jumlah');
    }

    /**
     * Tambahan soal khusus prodi (hanya berlaku bila jadwal ini menargetkan satu prodi
     * tertentu via `prodi_id`; diabaikan untuk jadwal umum).
     */
    public function totalSoalProdiMaksimum(): int
    {
        return $this->prodi_id ? $this->komposisi->sum('jumlah_prodi') : 0;
    }

    /**
     * Total soal yang akan didapat setiap peserta jadwal ini (umum + khusus prodi bila ada).
     */
    public function totalSoal(): int
    {
        return $this->totalSoalUmum() + $this->totalSoalProdiMaksimum();
    }
}
