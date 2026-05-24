<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kamus_validasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamus_id')->constrained('kamus')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('action', ['setuju', 'tolak']);
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Setiap user hanya bisa validasi satu kali per kamus
            $table->unique(['kamus_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamus_validasi');
    }
};
