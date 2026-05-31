<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kamus')) {
            Schema::create('kamus', function (Blueprint $table) {
                $table->id();
                $table->string('kata');
                $table->text('definisi');
                $table->string('audio')->nullable();
                $table->tinyInteger('status')->default(3);
                $table->text('catatan_validasi')->nullable();
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kamus_contoh')) {
            Schema::create('kamus_contoh', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kamus_id')->constrained('kamus')->onDelete('cascade');
                $table->text('contoh_kalimat');
                $table->text('arti_contoh_kalimat');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kamus_validasi')) {
            Schema::create('kamus_validasi', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kamus_id')->constrained('kamus')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->enum('action', ['setuju', 'tolak']);
                $table->text('catatan')->nullable();
                $table->timestamps();
                $table->unique(['kamus_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kamus_validasi');
        Schema::dropIfExists('kamus_contoh');
        Schema::dropIfExists('kamus');
    }
};
