<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoKetentuan extends Model
{
    use HasUuids;

    protected $table = 'promo_ketentuan';

    protected $fillable = [
        'promo_id',
        'jalur_id',
        'prodi_id',
        'kelas_id',
    ];

    public function promo(): BelongsTo
    {
        return $this->belongsTo(Promo::class, 'promo_id');
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
}
