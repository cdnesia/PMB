<?php

namespace App\Models;

use App\Models\Concerns\PunyaNamaTerjemahan;
use Illuminate\Database\Eloquent\Model;

class Agama extends Model
{
    use PunyaNamaTerjemahan;

    protected $table = 'agama';

    protected $fillable = ['kode', 'nama'];
}
