<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DaftarUlang extends Model
{
    use HasUuids;

    protected $table = 'daftar_ulang';

    protected $fillable = [
        'pendaftaran_id',
        'nominal',
        'status',
        'bukti_bayar',
        'file_name',
        'file_size',
        'tanggal_bayar',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'tanggal_bayar' => 'date',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }
}
