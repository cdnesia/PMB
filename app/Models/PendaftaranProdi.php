<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendaftaranProdi extends Model
{
    use HasUuids;

    protected $table = 'pendaftaran_prodi';

    protected $fillable = [
        'pendaftaran_id',
        'urutan',
        'prodi_id',
        'kelas_id',
        'status',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(KelasPerkuliahan::class, 'kelas_id');
    }
}
