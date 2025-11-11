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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modul_pembelajaran_id')
                  ->nullable()
                  ->constrained('modul_pembelajaran')
                  ->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->nullable()->unique()->after('title');
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->foreignId('master_media_music_quiz_id')
                  ->nullable()
                  ->constrained('master_media_music_quiz')
                  ->onDelete('set null');
            $table->integer('duration')->default(30); // durasi dalam menit
            $table->enum('type', ['modul', 'umum'])->default('umum');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->index('modul_pembelajaran_id');
            $table->index('type');
            $table->index('status');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
