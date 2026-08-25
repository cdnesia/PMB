<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtSesi extends Model
{
    use HasUuids;

    protected $table = 'cbt_sesi';

    protected $fillable = [
        'cbt_jadwal_id',
        'pendaftaran_id',
        'status',
        'soal_urutan',
        'skor',
        'jumlah_benar',
        'jumlah_salah',
        'jumlah_kosong',
        'started_at',
        'deadline_at',
        'finished_at',
        'finish_reason',
        'jumlah_pelanggaran',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'soal_urutan' => 'array',
            'skor' => 'decimal:2',
            'started_at' => 'datetime',
            'deadline_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(CbtJadwal::class, 'cbt_jadwal_id');
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function jawaban(): HasMany
    {
        return $this->hasMany(CbtJawaban::class, 'cbt_sesi_id');
    }

    public function pelanggaran(): HasMany
    {
        return $this->hasMany(CbtPelanggaran::class, 'cbt_sesi_id');
    }

    public function sudahSelesai(): bool
    {
        return $this->status === 'selesai';
    }

    public function sudahLewatDeadline(): bool
    {
        return now()->greaterThanOrEqualTo($this->deadline_at);
    }
}
