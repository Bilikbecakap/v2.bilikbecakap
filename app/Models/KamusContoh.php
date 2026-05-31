<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KamusContoh extends Model
{
    use HasFactory;

    protected $table = 'kamus_contoh';

    protected $fillable = [
        'kamus_id',
        'contoh_kalimat',
        'arti_contoh_kalimat',
    ];

    public function kamus()
    {
        return $this->belongsTo(Kamus::class, 'kamus_id');
    }
}
