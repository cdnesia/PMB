<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KelasPerkuliahan extends Model
{
    use HasUuids;

    protected $table = 'kelas_perkuliahan';

    protected $fillable = [
        'kode',
        'nama',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function prodi(): BelongsToMany
    {
        return $this->belongsToMany(Prodi::class, 'prodi_kelas_jalur', 'kelas_id', 'prodi_id');
    }

    public function jalurBiaya(): HasMany
    {
        return $this->hasMany(JalurKelas::class, 'kelas_id');
    }

    public function kuota(): HasMany
    {
        return $this->hasMany(Kuota::class, 'kelas_id');
    }
}
