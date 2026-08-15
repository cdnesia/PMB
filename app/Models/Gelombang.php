<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Gelombang extends Model
{
    use HasUuids;

    protected $table = 'gelombang';

    protected $fillable = [
        'tahun_id',
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_pengumuman',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'tanggal_pengumuman' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function tahun(): BelongsTo
    {
        return $this->belongsTo(TahunPenerimaan::class, 'tahun_id');
    }

    public function jalur(): BelongsToMany
    {
        return $this->belongsToMany(Jalur::class, 'gelombang_jalur', 'gelombang_id', 'jalur_id');
    }
}
