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
        Schema::create('gambar_galeri', function (Blueprint $table) {
            $table->id();
            $table->string('gambar');
            $table->foreignId('master_gambar_id')
                  ->constrained('master_gambar')
                  ->onDelete('cascade');
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->index('master_gambar_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gambar_galeri');
    }
};
