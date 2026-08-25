<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtPelanggaran extends Model
{
    use HasUuids;

    protected $table = 'cbt_pelanggaran';

    protected $fillable = [
        'cbt_sesi_id',
        'jenis',
        'keterangan',
        'terjadi_pada',
    ];

    protected function casts(): array
    {
        return [
            'terjadi_pada' => 'datetime',
        ];
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(CbtSesi::class, 'cbt_sesi_id');
    }
}
