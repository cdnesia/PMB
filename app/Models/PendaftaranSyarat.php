<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendaftaranSyarat extends Model
{
    use HasUuids;

    protected $table = 'pendaftaran_syarat';

    protected $fillable = [
        'pendaftaran_id',
        'syarat_jalur_id',
        'nilai',
        'file_path',
        'file_name',
        'file_size',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function syarat(): BelongsTo
    {
        return $this->belongsTo(SyaratJalur::class, 'syarat_jalur_id');
    }
}
