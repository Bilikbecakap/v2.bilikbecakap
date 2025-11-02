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
        Schema::create('modul_pembelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('master_modul')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->longText('content')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('pdf_file')->nullable();
            $table->string('video_embed')->nullable(); // YouTube embed URL
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('tanggal_publish')->nullable();
            $table->bigInteger('view_count')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for better performance
            $table->index(['status', 'tanggal_publish']);
            $table->index('view_count');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modul_pembelajaran');
    }
};