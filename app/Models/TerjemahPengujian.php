<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerjemahPengujian extends Model
{
    use HasFactory;

    protected $table = 'terjemah_pengujian';

    protected $fillable = [
        'teks_indonesia',
        'terjemahan_pengguna',
        'status',
        'created_by',
        'updated_by',
    ];

    // 1=menunggu, 2=menunggu finalisasi, 3=tervalidasi, 4=ditolak
    public function getStatusLabelAttribute(): string
    {
        return [
            1 => 'Menunggu',
            2 => 'Menunggu Finalisasi',
            3 => 'Tervalidasi',
            4 => 'Ditolak',
        ][$this->status] ?? 'Unknown';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function validasi()
    {
        return $this->hasOne(TerjemahPengujianValidasi::class, 'terjemah_pengujian_id');
    }
}
