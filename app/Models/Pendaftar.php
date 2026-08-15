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
        'jenis_tinggal',
        'jenis_tinggal_kode',
        'alat_transportasi',
        'alat_transportasi_kode',
        'pembiayaan',
        'pembiayaan_kode',
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
        'nama_ayah',
        'nama_ibu_kandung',
        'nama_wali',
        'nik_ayah',
        'nik_ibu',
        'nik_wali',
        'pekerjaan_ayah',
        'pekerjaan_ayah_kode',
        'pekerjaan_ibu',
        'pekerjaan_ibu_kode',
        'pekerjaan_wali',
        'pekerjaan_wali_kode',
        'penghasilan_ayah',
        'penghasilan_ayah_kode',
        'penghasilan_ibu',
        'penghasilan_ibu_kode',
        'penghasilan_wali',
        'penghasilan_wali_kode',
        'golongan_darah',
        'status_perkawinan',
        'kebutuhan_khusus',
        'penerima_kps',
        'nomor_kps',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'penerima_kps' => 'boolean',
            'agama_kode' => 'integer',
            'pekerjaan_ayah_kode' => 'integer',
            'pekerjaan_ibu_kode' => 'integer',
            'pekerjaan_wali_kode' => 'integer',
            'penghasilan_ayah_kode' => 'integer',
            'penghasilan_ibu_kode' => 'integer',
            'penghasilan_wali_kode' => 'integer',
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
