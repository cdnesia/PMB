<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenPendaftar extends Model
{
    use HasUuids;

    protected $table = 'dokumen_pendaftar';

    protected $fillable = [
        'pendaftaran_id',
        'dokumen_persyaratan_id',
        'nama',
        'file_path',
        'file_name',
        'file_size',
        'status',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function dokumenPersyaratan(): BelongsTo
    {
        return $this->belongsTo(DokumenPersyaratan::class, 'dokumen_persyaratan_id');
    }
}
