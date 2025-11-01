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
        Schema::create('artikel', function (Blueprint $table) {
            $table->id();$table->string('judul_indonesia')->nullable();
            $table->string('judul_melayu')->nullable();
            $table->string('judul_english')->nullable();
            $table->longText('konten_indonesia')->nullable();
            $table->longText('konten_melayu')->nullable();
            $table->longText('konten_english')->nullable();
            $table->string('slug')->unique();
            $table->unsignedBigInteger('kategori_id');
            $table->string('gambar_thumbnail')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->enum('status', ['draft','pending', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->datetime('tanggal_publish')->nullable();
            $table->integer('views_count')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            // Foreign keys
            $table->foreign('kategori_id')->references('id')->on('master_artikel')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['status', 'tanggal_publish']);
            $table->index('is_featured');
            $table->index('kategori_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artikel');
    }
};
