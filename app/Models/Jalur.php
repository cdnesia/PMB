<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jalur extends Model
{
    use HasUuids;

    protected $table = 'jalur';

    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'urutan',
        'biaya_pendaftaran',
        'requires_cbt',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'requires_cbt' => 'boolean',
            'biaya_pendaftaran' => 'decimal:2',
        ];
    }

    public function prodi(): BelongsToMany
    {
        return $this->belongsToMany(Prodi::class, 'prodi_kelas_jalur', 'jalur_id', 'prodi_id');
    }

    public function kelasPerkuliahan(): BelongsToMany
    {
        return $this->belongsToMany(KelasPerkuliahan::class, 'prodi_kelas_jalur', 'jalur_id', 'kelas_id');
    }

    public function kelasBiaya(): HasMany
    {
        return $this->hasMany(JalurKelas::class, 'jalur_id');
    }

    public function kuota(): HasMany
    {
        return $this->hasMany(Kuota::class, 'jalur_id');
    }

    public function dokumenPersyaratan(): HasMany
    {
        return $this->hasMany(DokumenPersyaratan::class, 'jalur_id');
    }

    public function syarat(): HasMany
    {
        return $this->hasMany(SyaratJalur::class, 'jalur_id');
    }

    public function cbtSoal(): HasMany
    {
        return $this->hasMany(CbtSoal::class, 'jalur_id');
    }

    public function cbtJadwal(): HasMany
    {
        return $this->hasMany(CbtJadwal::class, 'jalur_id');
    }
}
