<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wilayah extends Model
{
    use HasUuids;

    protected $table = 'wilayah';

    protected $fillable = ['kode', 'nama', 'level', 'parent_id'];

    public const LEVEL_NEGARA = 'negara';
    public const LEVEL_PROVINSI = 'provinsi';
    public const LEVEL_KOTA = 'kota';
    public const LEVEL_KECAMATAN = 'kecamatan';
    public const LEVEL_KELURAHAN = 'kelurahan';

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
