<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerjemahPengujianValidasi extends Model
{
    use HasFactory;

    protected $table = 'terjemah_pengujian_validasi';

    protected $fillable = [
        'terjemah_pengujian_id',
        'user_id',
        'terjemahan_koreksi',
        'catatan',
    ];

    public function terjemah()
    {
        return $this->belongsTo(TerjemahPengujian::class, 'terjemah_pengujian_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
