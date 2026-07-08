<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackTerjemahan extends Model
{
    protected $table = 'feedback_terjemahan';

    protected $fillable = [
        'tipe',
        'arah_terjemahan',
        'teks_input',
        'terjemahan_asli',
        'terjemahan_benar',
        'keterangan',
        'ip_address',
    ];
}
