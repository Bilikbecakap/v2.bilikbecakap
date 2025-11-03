<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quizzes extends Model
{
    use HasFactory;

    protected $table = 'quizzes';

    protected $fillable = [
        'modul_pembelajaran_id',
        'title',
        'description',
        'duration',
        'type',
        'status',
    ];

    protected $casts = [
        'duration' => 'integer',
    ];

    // Relasi ke Modul Pembelajaran
    public function modulPembelajaran()
    {
        return $this->belongsTo(ModulPembelajaran::class, 'modul_pembelajaran_id');
    }

    // Relasi ke Questions
    public function questions()
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id')->orderBy('order');
    }

    // Relasi ke Attempts
    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id');
    }

    // Helper: Cek apakah quiz modul atau umum
    public function isModulQuiz()
    {
        return $this->type === 'modul' && $this->modul_pembelajaran_id !== null;
    }

    // Helper: Total soal
    public function getTotalQuestionsAttribute()
    {
        return $this->questions()->count();
    }
}
