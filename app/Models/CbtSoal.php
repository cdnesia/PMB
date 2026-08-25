<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtSoal extends Model
{
    use HasUuids;

    protected $table = 'cbt_soal';

    protected $fillable = [
        'jalur_id',
        'prodi_id',
        'kategori',
        'pertanyaan',
        'pilihan_a',
        'pilihan_b',
        'pilihan_c',
        'pilihan_d',
        'pilihan_e',
        'kunci_jawaban',
        'bobot',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'bobot' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function jalur(): BelongsTo
    {
        return $this->belongsTo(Jalur::class, 'jalur_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    /**
     * Daftar pilihan jawaban a-e (huruf => teks), melewati pilihan_e jika kosong.
     */
    public function pilihan(): array
    {
        return array_filter([
            'a' => $this->pilihan_a,
            'b' => $this->pilihan_b,
            'c' => $this->pilihan_c,
            'd' => $this->pilihan_d,
            'e' => $this->pilihan_e,
        ], fn ($teks) => filled($teks));
    }
}
