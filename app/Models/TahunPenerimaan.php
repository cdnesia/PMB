<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunPenerimaan extends Model
{
    use HasUuids;

    protected $table = 'tahun_penerimaan';

    protected $fillable = [
        'kode',
        'nama',
        'status',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function gelombang(): HasMany
    {
        return $this->hasMany(Gelombang::class, 'tahun_id');
    }

    public function kuota(): HasMany
    {
        return $this->hasMany(Kuota::class, 'tahun_id');
    }
}
