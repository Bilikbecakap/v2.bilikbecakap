<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizOption extends Model
{
    use HasFactory;

    protected $table = 'quiz_options';

    protected $fillable = [
        'quiz_question_id',
        'option_text',
        'is_correct',
        'order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'order' => 'integer',
    ];

    // Relasi ke Question
    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }

    // Relasi ke Answers
    public function answers()
    {
        return $this->hasMany(QuizAnswer::class);
    }
}
