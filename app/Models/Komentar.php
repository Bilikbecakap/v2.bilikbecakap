<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komentar extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kontak',
        'isi_komentar',
        'commentable_id',
        'commentable_type',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Relasi polymorphic: komentar bisa milik Artikel atau ModulPembelajaran
     */
    public function commentable()
    {
        return $this->morphTo();
    }
}