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
        'slug',
        'description',
        'thumbnail',
        'master_media_music_quiz_id',
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

    // TAMBAH: Relasi ke Master Media Music Quiz
    public function masterMediaMusicQuiz()
    {
        return $this->belongsTo(MasterMediaMusicQuiz::class, 'master_media_music_quiz_id');
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

    // UBAH: Ambil music dari relasi
    public function getMusicUrlAttribute()
    {
        if ($this->masterMediaMusicQuiz && $this->masterMediaMusicQuiz->audio) {
            return $this->masterMediaMusicQuiz->audio_url;
        }

        return null;
    }

    public function deleteThumbnail()
    {
        if ($this->thumbnail && Storage::disk('public')->exists($this->thumbnail)) {
            Storage::disk('public')->delete($this->thumbnail);
        }
    }
}