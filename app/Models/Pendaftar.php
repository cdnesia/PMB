<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pendaftar extends Model
{
    use HasUuids;

    protected $table = 'pendaftar';

    protected $fillable = [
        'pendaftaran_id',
        'user_id',
        'nik',
        'nisn',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'agama_kode',
        'kewarganegaraan',
        'negara',
        'negara_kode',
        'alamat',
        'rt',
        'rw',
        'dusun',
        'provinsi',
        'provinsi_kode',
        'kota',
        'kota_kode',
        'kecamatan',
        'kecamatan_kode',
        'kelurahan',
        'kelurahan_kode',
        'kode_pos',
        'asal_sekolah',
        'tahun_lulus',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'agama_kode' => 'integer',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
