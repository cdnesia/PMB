<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prodi extends Model
{
    use HasUuids;

    protected $table = 'prodi';

    protected $fillable = [
        'kode',
        'nama',
        'jenjang',
        'fakultas',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function kelasPerkuliahan(): BelongsToMany
    {
        return $this->belongsToMany(KelasPerkuliahan::class, 'prodi_kelas_jalur', 'prodi_id', 'kelas_id');
    }

    public function jalur(): BelongsToMany
    {
        return $this->belongsToMany(Jalur::class, 'prodi_kelas_jalur', 'prodi_id', 'jalur_id');
    }

    public function kuota(): HasMany
    {
        return $this->hasMany(Kuota::class, 'prodi_id');
    }
}
