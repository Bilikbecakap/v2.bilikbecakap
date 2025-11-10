<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kontak extends Model
{
    protected $table = 'kontaks';

    protected $fillable = [
        'nama',
        'email',
        'nomor_telepon',
        'subjek',
        'pesan',
    ];
}