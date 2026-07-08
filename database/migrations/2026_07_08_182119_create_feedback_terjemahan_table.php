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
        Schema::create('feedback_terjemahan', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['akurat', 'kurang_tepat']);
            $table->string('arah_terjemahan');
            $table->text('teks_input');
            $table->text('terjemahan_asli');
            $table->text('terjemahan_benar')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_terjemahan');
    }
};
