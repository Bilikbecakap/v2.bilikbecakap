<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Quizzes extends Model
{
    use HasFactory;

    protected $table = 'quizzes';

    protected $fillable = [
        'modul_pembelajaran_id',
        'title',
        'description',
        'thumbnail',
        'music',  
        'duration',
        'type',
        'status',
    ];

    protected $casts = [
        'duration' => 'integer',
    ];

    protected $appends = ['thumbnail_url', 'music_url'];

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

    public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail) {
            return null;
        }

        if (Storage::disk('public')->exists($this->thumbnail)) {
            return asset('storage/' . $this->thumbnail);
        }

        return null;
    }

    public function getMusicUrlAttribute()
    {
        if (!$this->music) {
            return null;
        }

        if (Storage::disk('public')->exists($this->music)) {
            return asset('storage/' . $this->music);
        }

        return null;
    }

    public function deleteThumbnail()
    {
        if ($this->thumbnail && Storage::disk('public')->exists($this->thumbnail)) {
            Storage::disk('public')->delete($this->thumbnail);
        }
    }

    public function deleteMusic()
    {
        if ($this->music && Storage::disk('public')->exists($this->music)) {
            Storage::disk('public')->delete($this->music);
        }
    }

}
