<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JalurKelas extends Model
{
    use HasUuids;

    protected $table = 'jalur_kelas';

    protected $fillable = [
        'jalur_id',
        'kelas_id',
        'biaya_pendaftaran',
    ];

    protected function casts(): array
    {
        return [
            'biaya_pendaftaran' => 'decimal:2',
        ];
    }

    public function jalur(): BelongsTo
    {
        return $this->belongsTo(Jalur::class, 'jalur_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(KelasPerkuliahan::class, 'kelas_id');
    }
}
