<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komentars', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Nama pengunjung (wajib)
            $table->string('kontak')->nullable(); // Bisa diganti jadi 'kontak' jika ingin lebih fleksibel
            // Jika Anda ingin mendukung WhatsApp/telepon juga:
            // $table->string('kontak')->nullable(); 

            $table->text('isi_komentar');
            
            // Polymorphic relationship: bisa ke artikel atau modul_pembelajaran
            $table->unsignedBigInteger('commentable_id');
            $table->string('commentable_type'); // Contoh: 'App\Models\Artikel' atau 'App\Models\ModulPembelajaran'

            // Moderasi (opsional tapi sangat disarankan)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->timestamps();

            // Index untuk performa
            $table->index(['commentable_type', 'commentable_id']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komentars');
    }
};