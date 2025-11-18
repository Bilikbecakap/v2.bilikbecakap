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
        // 1. Tambah kolom di quiz_questions untuk support fill_blank
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->enum('question_type', ['multiple_choice', 'fill_blank'])
                  ->default('multiple_choice')
                  ->after('question');
            
            $table->index('question_type');
        });

        // 2. Tambah kolom di quiz_attempts untuk duel mode
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->enum('game_mode', ['single', 'duel'])
                  ->default('single')
                  ->after('quiz_id');
            $table->string('player1_name')->nullable()->after('participant_name');
            $table->string('player2_name')->nullable()->after('player1_name');
            $table->integer('player1_score')->default(0)->after('player2_name');
            $table->integer('player2_score')->default(0)->after('player1_score');
            $table->enum('winner', ['player1', 'player2', 'draw'])->nullable()->after('player2_score');
            
            // Index untuk performa
            $table->index('game_mode');
            $table->index('winner');
        });

        // 3. Tambah kolom di quizzes untuk flag duel enabled
        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('is_duel_enabled')
                  ->default(false)
                  ->after('type');
            
            $table->index('is_duel_enabled');
        });

        // 4. Tambah kolom di quiz_answers untuk support fill_blank (text answer)
        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->text('text_answer')->nullable()->after('quiz_option_id');
            $table->enum('answer_type', ['option', 'text'])->default('option')->after('text_answer');
            
            $table->index('answer_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropIndex(['question_type']);
            $table->dropColumn('question_type');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex(['game_mode']);
            $table->dropIndex(['winner']);
            $table->dropColumn([
                'game_mode',
                'player1_name',
                'player2_name',
                'player1_score',
                'player2_score',
                'winner',
            ]);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex(['is_duel_enabled']);
            $table->dropColumn('is_duel_enabled');
        });

        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->dropIndex(['answer_type']);
            $table->dropColumn([
                'text_answer',
                'answer_type',
            ]);
        });
    }
};