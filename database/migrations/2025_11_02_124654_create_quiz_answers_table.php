<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')
                  ->constrained('quiz_attempts')
                  ->onDelete('cascade');
            $table->foreignId('quiz_question_id')
                  ->constrained('quiz_questions')
                  ->onDelete('cascade');
            $table->foreignId('quiz_option_id')
                  ->nullable()
                  ->constrained('quiz_options')
                  ->onDelete('cascade');
            $table->boolean('is_correct');
            $table->timestamps();
            
            $table->index('quiz_attempt_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
