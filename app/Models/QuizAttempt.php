<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $table = 'quiz_attempts';

    protected $fillable = [
        'quiz_id',
        'game_mode',
        'participant_name',
        'player1_name',     
        'player2_name',     
        'player1_score',    
        'player2_score',    
        'winner',           
        'score',
        'correct_answers',
        'total_questions',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'correct_answers' => 'integer',
        'total_questions' => 'integer',
        'player1_score' => 'integer',  
        'player2_score' => 'integer', 
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relasi ke Quiz
    public function quiz()
    {
        return $this->belongsTo(Quizzes::class);
    }

    // Relasi ke Answers
    public function answers()
    {
        return $this->hasMany(QuizAnswer::class);
    }

    // Helper: Hitung persentase
    public function getScorePercentageAttribute()
    {
        if ($this->total_questions == 0) {
            return 0;
        }
        return round(($this->correct_answers / $this->total_questions) * 100, 2);
    }

    // Helper: Durasi pengerjaan
    public function getDurationAttribute()
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInMinutes($this->completed_at);
        }
        return 0;
    }

    public function isDuelMode()
    {
        return $this->game_mode === 'duel';
    }

    public function isSingleMode()
    {
        return $this->game_mode === 'single';
    }

    public function getWinnerNameAttribute()
    {
        if (!$this->winner) return null;
        
        return $this->winner === 'player1' 
            ? $this->player1_name 
            : $this->player2_name;
    }

    public function hasWinner()
    {
        return !is_null($this->winner);
    }
}
