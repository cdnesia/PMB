<?php

namespace App\Models;

use App\Models\Concerns\PunyaNamaTerjemahan;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyaratJalur extends Model
{
    use HasUuids;
    use PunyaNamaTerjemahan;

    protected $table = 'syarat_jalur';

    protected $fillable = ['jalur_id', 'tipe', 'kode', 'nama', 'wajib', 'is_active'];

    protected function casts(): array
    {
        return [
            'wajib' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function jalur(): BelongsTo
    {
        return $this->belongsTo(Jalur::class, 'jalur_id');
    }
}
