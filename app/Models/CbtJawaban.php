<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtJawaban extends Model
{
    use HasUuids;

    protected $table = 'cbt_jawaban';

    protected $fillable = [
        'cbt_sesi_id',
        'cbt_soal_id',
        'jawaban',
        'is_benar',
        'ragu_ragu',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'is_benar' => 'boolean',
            'ragu_ragu' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(CbtSesi::class, 'cbt_sesi_id');
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(CbtSoal::class, 'cbt_soal_id');
    }
}
