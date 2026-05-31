<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kamus', function (Blueprint $table) {
            // Kelas kata: a, adv, n, num, p, pron, v
            $table->string('pos', 10)->nullable()->after('kata');
        });

        Schema::table('kamus_contoh', function (Blueprint $table) {
            $table->text('arti_contoh_kalimat')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('kamus', function (Blueprint $table) {
            $table->dropColumn('pos');
        });

        Schema::table('kamus_contoh', function (Blueprint $table) {
            $table->text('arti_contoh_kalimat')->nullable(false)->change();
        });
    }
};
