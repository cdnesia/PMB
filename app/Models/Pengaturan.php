<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Pengaturan extends Model
{
    use HasUuids;

    protected $table = 'pengaturan';

    protected $fillable = ['key', 'value'];

    /**
     * Ambil nilai pengaturan berdasarkan key.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $row = static::where('key', $key)->first();

        return $row ? $row->value : $default;
    }

    /**
     * Simpan / perbarui nilai pengaturan.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
