<?php

namespace App\Models;

use App\Models\Concerns\PunyaNamaTerjemahan;
use Illuminate\Database\Eloquent\Model;

class Pekerjaan extends Model
{
    use PunyaNamaTerjemahan;

    protected $table = 'pekerjaan';

    protected $fillable = ['kode', 'nama'];
}
