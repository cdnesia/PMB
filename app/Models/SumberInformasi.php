<?php

namespace App\Models;

use App\Models\Concerns\PunyaNamaTerjemahan;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SumberInformasi extends Model
{
    use HasUuids;
    use PunyaNamaTerjemahan;

    protected $table = 'sumber_informasi';

    protected $fillable = [
        'kode',
        'nama',
        'urutan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
