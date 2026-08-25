<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtJadwalKomposisi extends Model
{
    use HasUuids;

    protected $table = 'cbt_jadwal_komposisi';

    protected $fillable = [
        'cbt_jadwal_id',
        'kategori',
        'jumlah',
        'jumlah_prodi',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(CbtJadwal::class, 'cbt_jadwal_id');
    }
}
