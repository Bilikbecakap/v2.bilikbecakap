<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAnswer extends Model
{
    use HasFactory;

    protected $table = 'quiz_answers';

    protected $fillable = [
        'quiz_attempt_id',
        'quiz_question_id',
        'quiz_option_id',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    // Relasi ke Attempt
    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    // Relasi ke Question
    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }

    // Relasi ke Option (jawaban yang dipilih user)
    public function selectedOption()
    {
        return $this->belongsTo(QuizOption::class, 'quiz_option_id');
    }
}
