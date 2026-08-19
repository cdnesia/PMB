<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Referrer extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'referrer';

    protected $fillable = [
        'user_id',
        'kode',
        'jenis',
        'nama_instansi',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(Pendaftaran::class, 'referrer_id');
    }

    public function isMitra(): bool
    {
        return $this->jenis === 'mitra';
    }
}
