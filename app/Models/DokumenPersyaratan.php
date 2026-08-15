<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenPersyaratan extends Model
{
    use HasUuids;

    protected $table = 'dokumen_persyaratan';

    protected $fillable = [
        'jalur_id',
        'prodi_id',
        'nama',
        'wajib',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'wajib' => 'boolean',
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
}
