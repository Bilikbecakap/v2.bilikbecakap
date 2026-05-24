<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KamusValidasi extends Model
{
    use HasFactory;

    protected $table = 'kamus_validasi';

    protected $fillable = [
        'kamus_id',
        'user_id',
        'action',
        'catatan',
    ];

    public function kamus()
    {
        return $this->belongsTo(Kamus::class, 'kamus_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
