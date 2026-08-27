<?php

namespace App\Models;

use App\Models\Concerns\PunyaNamaTerjemahan;
use Illuminate\Database\Eloquent\Model;

class JenisKelamin extends Model
{
    use PunyaNamaTerjemahan;

    protected $table = 'jenis_kelamin';

    protected $fillable = ['kode', 'nama'];
}
