<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kuota extends Model
{
    use HasUuids;

    protected $table = 'kuota';

    protected $fillable = [
        'tahun_id',
        'jalur_id',
        'prodi_id',
        'kelas_id',
        'jumlah',
        'terpakai',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'jumlah' => 'integer',
            'terpakai' => 'integer',
        ];
    }

    public function tahun(): BelongsTo
    {
        return $this->belongsTo(TahunPenerimaan::class, 'tahun_id');
    }

    public function jalur(): BelongsTo
    {
        return $this->belongsTo(Jalur::class, 'jalur_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(KelasPerkuliahan::class, 'kelas_id');
    }

    public function getSisaAttribute(): int
    {
        return max(0, $this->jumlah - $this->terpakai);
    }

    public function getPenuhAttribute(): bool
    {
        return $this->terpakai >= $this->jumlah;
    }
}
