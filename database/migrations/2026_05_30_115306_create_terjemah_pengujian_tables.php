<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terjemah_pengujian', function (Blueprint $table) {
            $table->id();
            $table->text('teks_indonesia');
            $table->text('terjemahan_pengguna');
            $table->tinyInteger('status')->default(1);
            // 1=menunggu, 2=menunggu finalisasi, 3=tervalidasi, 4=ditolak
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('terjemah_pengujian_validasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('terjemah_pengujian_id')->constrained('terjemah_pengujian')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('terjemahan_koreksi')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['terjemah_pengujian_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terjemah_pengujian_validasi');
        Schema::dropIfExists('terjemah_pengujian');
    }
};
