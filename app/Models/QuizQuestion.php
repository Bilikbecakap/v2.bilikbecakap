<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $table = 'quiz_questions';

    protected $fillable = [
        'quiz_id',
        'question',
        'question_type',
        'correct_answer',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    // Relasi ke Quiz
    public function quiz()
    {
        return $this->belongsTo(Quizzes::class);
    }

    // Relasi ke Options
    public function options()
    {
        return $this->hasMany(QuizOption::class)->orderBy('order');
    }

    // Relasi ke Answers
    public function answers()
    {
        return $this->hasMany(QuizAnswer::class);
    }

    // Helper: Ambil jawaban yang benar
    public function correctOption()
    {
        return $this->hasOne(QuizOption::class)->where('is_correct', true);
    }
    
    public function isFillBlank()
    {
        return $this->question_type === 'fill_blank';
    }

    public function isMultipleChoice()
    {
        return $this->question_type === 'multiple_choice';
    }
}
